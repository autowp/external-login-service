<?php

declare(strict_types=1);

namespace AutowpTest\ExternalLoginService;

use Autowp\ExternalLoginService\Github;
use Autowp\ExternalLoginService\PluginManager;
use Autowp\ExternalLoginService\Result;
use Exception;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use League\OAuth2\Client;

class GithubTest extends AbstractHttpControllerTestCase
{
    protected string $appConfigPath = __DIR__ . '/_files/config/application.config.php';

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        if (! $this->appConfigPath) {
            throw new Exception("Application config path not provided");
        }

        $this->setApplicationConfig(include $this->appConfigPath);

        parent::setUp();
    }

    private function mockProvider(): void
    {
        $providerMock = $this->getMockBuilder(Client\Provider\Github::class)
            ->onlyMethods(['getResourceOwner', 'getAccessToken'])
            ->setConstructorArgs([[]])
            ->getMock();

        $providerMock->method('getResourceOwner')->willReturnCallback(function () {
            return new Client\Provider\GithubResourceOwner([
                'id'         => 'user_id',
                'name'       => 'UserName',
                'html_url'   => 'http://example.com/user_id',
                'avatar_url' => 'http://example.com/user_id.jpg',
            ]);
        });

        $providerMock->method('getAccessToken')->willReturnCallback(function () {
            return new Client\Token\AccessToken([
                'access_token' => 'returned_access_token',
            ]);
        });

        $service = $this->getService();

        $service->setProvider($providerMock);
    }

    private function getService(): Github
    {
        $manager = $this->getApplicationServiceLocator()->get('ExternalLoginServiceManager');

        $this->assertInstanceOf(PluginManager::class, $manager);

        $service = $manager->get('github');

        $this->assertInstanceOf(Github::class, $service);

        return $service;
    }

    public function testUrls(): void
    {
        $service = $this->getService();

        $url = $service->getLoginUrl();

        $this->assertRegExp(
            '|^https://github\.com/login/oauth/authorize\?state=[a-z0-9]+&scope='
                . '&response_type=code&approval_prompt=auto'
                . '&redirect_uri=http%3A%2F%2Fexample\.com%2Fcallback&client_id=xxxx$|iu',
            $url
        );
    }

    public function testFriendsUrl(): void
    {
        $this->expectException(Exception::class);

        $service = $this->getService();

        $service->getFriendsUrl();
    }

    public function testThrowsCredentialRequired(): void
    {
        $this->expectException(Client\Provider\Exception\GithubIdentityProviderException::class);

        $service = $this->getService();

        $service->setAccessToken('example_access_token');
        $service->getData([]);
    }

    public function testGetData(): void
    {
        $this->mockProvider();

        $service = $this->getService();

        $service->setAccessToken('example_access_token');

        $data = $service->getData([]);

        $this->assertInstanceOf(Result::class, $data);
        $this->assertEquals('UserName', $data->getName());
        $this->assertEquals('user_id', $data->getExternalId());
        $this->assertEquals('http://example.com/user_id', $data->getProfileUrl());
        $this->assertEquals('http://example.com/user_id.jpg', $data->getPhotoUrl());
    }

    public function testCallback(): void
    {
        $this->mockProvider();

        $service = $this->getService();

        $accessToken = $service->callback([
            'code' => 'zzzz',
        ]);

        $this->assertEquals('returned_access_token', $accessToken);
    }
}

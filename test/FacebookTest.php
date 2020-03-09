<?php

declare(strict_types=1);

namespace AutowpTest\ExternalLoginService;

use Autowp\ExternalLoginService\Facebook;
use Autowp\ExternalLoginService\PluginManager;
use Autowp\ExternalLoginService\Provider\FacebookProvider;
use Autowp\ExternalLoginService\Result;
use Exception;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use League\OAuth2\Client;

class FacebookTest extends AbstractHttpControllerTestCase
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
        $providerMock = $this->getMockBuilder(FacebookProvider::class)
            ->onlyMethods(['getResourceOwner', 'getAccessToken'])
            ->setConstructorArgs([
                [
                    'graphApiVersion' => 'v2.10',
                ],
            ])
            ->getMock();

        $providerMock->method('getResourceOwner')->willReturnCallback(function () {
            return new Client\Provider\FacebookUser([
                'id'   => 'user_id',
                'name' => 'UserName',
                'link' => 'http://example.com/user_id',
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

    private function getService(): Facebook
    {
        $manager = $this->getApplicationServiceLocator()->get('ExternalLoginServiceManager');

        $this->assertInstanceOf(PluginManager::class, $manager);

        $service = $manager->get('facebook');

        $this->assertInstanceOf(Facebook::class, $service);

        return $service;
    }

    public function testUrls(): void
    {
        $service = $this->getService();

        $url = $service->getLoginUrl();

        $this->assertRegExp(
            '|^https://www\.facebook\.com/v2\.10/dialog/oauth'
                . '\?scope=public_profile%2Cuser_friends&state=[a-z0-9]+&'
                . 'response_type=code&approval_prompt=auto'
                . '&redirect_uri=http%3A%2F%2Fexample.com%2Fcallback&client_id=xxxx$|iu',
            $url
        );
    }

    public function testFriendsUrl(): void
    {
        $service = $this->getService();

        $url = $service->getFriendsUrl();

        $this->assertRegExp(
            '|^https://www\.facebook\.com/v2\.10/dialog/oauth'
                . '\?scope=public_profile%2Cuser_friends&state=[a-z0-9]+&'
                . 'response_type=code&approval_prompt=auto'
                . '&redirect_uri=http%3A%2F%2Fexample.com%2Fcallback&client_id=xxxx$|iu',
            $url
        );
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
        $this->assertEquals('https://graph.facebook.com/user_id/picture?type=large', $data->getPhotoUrl());
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

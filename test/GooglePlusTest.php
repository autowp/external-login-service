<?php

declare(strict_types=1);

namespace AutowpTest\ExternalLoginService;

use Autowp\ExternalLoginService\GooglePlus;
use Autowp\ExternalLoginService\PluginManager;
use Autowp\ExternalLoginService\Result;
use Exception;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use League\OAuth2\Client;

class GooglePlusTest extends AbstractHttpControllerTestCase
{
    protected string $appConfigPath = __DIR__ . '/_files/config/application.config.php';

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
        $providerMock = $this->getMockBuilder(GooglePlus::class)
            ->setMethods(['getResourceOwner', 'getAccessToken'])
            ->setConstructorArgs([[]])
            ->getMock();

        $providerMock->method('getResourceOwner')->willReturnCallback(function () {
            return new Client\Provider\GoogleUser([
                'id'          => 'user_id',
                'displayName' => 'UserName',
                'url'         => 'http://example.com/user_id',
                'image'       => [
                    'url' => 'https://lh5.googleusercontent.com/photo.jpg?sz=50',
                ],
                'gender'      => 'male',
                'language'    => 'en',
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

    private function getService(): GooglePlus
    {
        $manager = $this->getApplicationServiceLocator()->get('ExternalLoginServiceManager');

        $this->assertInstanceOf(PluginManager::class, $manager);

        $service = $manager->get('google-plus');

        $this->assertInstanceOf(GooglePlus::class, $service);

        return $service;
    }

    public function testUrls(): void
    {
        $service = $this->getService();

        $url = $service->getLoginUrl();

        $this->assertRegExp(
            '|^https://accounts\.google\.com/o/oauth2/auth'
                . '\?scope='
                    . 'https%3A%2F%2Fwww\.googleapis\.com%2Fauth%2Fplus\.me%20'
                    . 'https%3A%2F%2Fwww\.googleapis\.com%2Fauth%2Fuserinfo\.email%20'
                    . 'https%3A%2F%2Fwww\.googleapis\.com%2Fauth%2Fuserinfo\.profile'
                . '&state=[a-z0-9]+&response_type=code&approval_prompt=auto'
                . '&redirect_uri=http%3A%2F%2Fexample.com%2Fcallback&client_id=xxxx&authuser=-1$|iu',
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
        $this->expectException(Client\Provider\Exception\IdentityProviderException::class);

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
        $this->assertEquals('https://lh5.googleusercontent.com/photo.jpg', $data->getPhotoUrl());
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

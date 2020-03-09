<?php

declare(strict_types=1);

namespace AutowpTest\ExternalLoginService;

use Autowp\ExternalLoginService\PluginManager;
use Autowp\ExternalLoginService\Result;
use Autowp\ExternalLoginService\Vk;
use Exception;
use J4k\OAuth2\Client\Provider\VkontakteUser;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use League\OAuth2\Client;

class VkTest extends AbstractHttpControllerTestCase
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
        $providerMock = $this->getMockBuilder(Vk::class)
            ->setMethods(['getResourceOwner', 'getAccessToken'])
            ->setConstructorArgs([[]])
            ->getMock();

        $providerMock->method('getResourceOwner')->willReturnCallback(function () {
            return new VkontakteUser([
                'id'             => 'user_id',
                'first_name'     => 'User',
                'last_name'      => 'Name',
                'screen_name'    => 'user_id',
                'photo_max_orig' => 'http://example.com/user_id.jpg',
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

    private function getService(): Vk
    {
        $manager = $this->getApplicationServiceLocator()->get('ExternalLoginServiceManager');

        $this->assertInstanceOf(PluginManager::class, $manager);

        $service = $manager->get('vk');

        $this->assertInstanceOf(Vk::class, $service);

        return $service;
    }

    public function testUrl(): void
    {
        $service = $this->getService();

        $url = $service->getLoginUrl();

        $this->assertRegExp(
            '|^https://oauth\.vk\.com/authorize'
                . '\?state=[a-z0-9]+&scope=status&response_type=code'
                . '&approval_prompt=auto&redirect_uri=http%3A%2F%2Fexample.com%2Fcallback'
                . '&client_id=xxxx$|iu',
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
        $this->assertEquals('User Name', $data->getName());
        $this->assertEquals('user_id', $data->getExternalId());
        $this->assertEquals('http://vk.com/user_id', $data->getProfileUrl());
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

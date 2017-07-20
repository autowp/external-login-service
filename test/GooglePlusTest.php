<?php

namespace AutowpTest\ExternalLoginService;

use League\OAuth2\Client;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

use Autowp\ExternalLoginService\GooglePlus;
use Autowp\ExternalLoginService\PluginManager;
use Autowp\ExternalLoginService\Result;

class GooglePlusTest extends AbstractHttpControllerTestCase
{
    protected $appConfigPath = __DIR__ . '/_files/config/application.config.php';

    protected function setUp()
    {
        if (! $this->appConfigPath) {
            throw new \Exception("Application config path not provided");
        }

        $this->setApplicationConfig(include $this->appConfigPath);

        parent::setUp();
    }

    private function mockProvider()
    {
        $providerMock = $this->getMockBuilder(\League\OAuth2\Client\Provider\Google::class)
            ->setMethods(['getResourceOwner', 'getAccessToken'])
            ->setConstructorArgs([[]])
            ->getMock();

        $providerMock->method('getResourceOwner')->willReturnCallback(function () {
            return new Client\Provider\GoogleUser([
                'id'          => 'user_id',
                'displayName' => 'UserName',
                'url'         => 'http://example.com/user_id',
                'image' => [
                    'url' => 'http://example.com/user_id.jpg'
                ],
                'gender'      => 'male',
                'language'    => 'en'
            ]);
        });

        $providerMock->method('getAccessToken')->willReturnCallback(function () {
            return new Client\Token\AccessToken([
                'access_token'  => 'returned_access_token'
            ]);
        });

        $service = $this->getService();

        $service->setProvider($providerMock);
    }

    /**
     * @return GooglePlus
     */
    private function getService()
    {
        $manager = $this->getApplicationServiceLocator()->get('ExternalLoginServiceManager');

        $this->assertInstanceOf(PluginManager::class, $manager);

        $service = $manager->get('google-plus');

        $this->assertInstanceOf(GooglePlus::class, $service);

        return $service;
    }

    public function testUrls()
    {
        $service = $this->getService();

        $loginUrl = $service->getLoginUrl();

        $this->assertRegExp(
            '|https://accounts\.google\.com/o/oauth2/auth' .
                '\?scope='.
                    'https%3A%2F%2Fwww\.googleapis\.com%2Fauth%2Fplus\.me%20' .
                    'https%3A%2F%2Fwww\.googleapis\.com%2Fauth%2Fuserinfo\.email%20'.
                    'https%3A%2F%2Fwww\.googleapis\.com%2Fauth%2Fuserinfo\.profile' .
                '&state=[a-z0-9]+&response_type=code&approval_prompt=auto' .
                '&redirect_uri=http%3A%2F%2Fexample.com%2F&client_id=xxxx&authuser=-1|iu',
            $loginUrl
        );
    }

    public function testThrowsCredentialRequired()
    {
        $this->expectException(Client\Provider\Exception\IdentityProviderException::class);

        $service = $this->getService();

        $service->setAccessToken('example_access_token');
        $service->getData([]);
    }

    public function testGetData()
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

    public function testCallback()
    {
        $this->mockProvider();

        $service = $this->getService();

        $accessToken = $service->callback([
            'code' => 'zzzz'
        ]);

        $this->assertEquals('returned_access_token', $accessToken);
    }
}

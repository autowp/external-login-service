<?php

namespace AutowpTest\ExternalLoginService;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\FacebookUser;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

use Autowp\ExternalLoginService\Facebook;
use Autowp\ExternalLoginService\PluginManager;
use Autowp\ExternalLoginService\Result;

class FacebookTest extends AbstractHttpControllerTestCase
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
        $providerMock = $this->getMockBuilder(\Autowp\ExternalLoginService\Provider\Facebook::class)
            ->setConstructorArgs([[
                'graphApiVersion' => 'v2.10'
            ]])
            ->getMock();

        $providerMock->method('getResourceOwner')->willReturnCallback(function () {
            return new FacebookUser([
                'id'         => 'user_id',
                'name'       => 'UserName',
                'link'       => 'http://example.com/user_id'
            ]);
        });

        $service = $this->getService();

        $service->setProvider($providerMock);
    }

    /**
     * @return Facebook
     */
    private function getService()
    {
        $manager = $this->getApplicationServiceLocator()->get('ExternalLoginServiceManager');

        $this->assertInstanceOf(PluginManager::class, $manager);

        $service = $manager->get('facebook');

        $this->assertInstanceOf(Facebook::class, $service);

        return $service;
    }

    public function testUrls()
    {
        $service = $this->getService();

        $loginUrl = $service->getLoginUrl();

        $this->assertRegExp(
            '|https://www\.facebook\.com/v2\.10/dialog/oauth' .
                '\?scope=public_profile%2Cuser_friends&state=[a-z0-9]+&' .
                'response_type=code&approval_prompt=auto' .
                '&redirect_uri=http%3A%2F%2Fexample.com%2F&client_id=xxxx|iu',
            $loginUrl
        );
    }

    public function testThrowsCredentialRequired()
    {
        $this->expectException(IdentityProviderException::class);

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
        $this->assertEquals('https://graph.facebook.com/user_id/picture?type=large', $data->getPhotoUrl());
    }
}

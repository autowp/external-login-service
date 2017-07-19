<?php

namespace AutowpTest\ExternalLoginService;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\LinkedInResourceOwner;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

use Autowp\ExternalLoginService\Linkedin;
use Autowp\ExternalLoginService\PluginManager;
use Autowp\ExternalLoginService\Result;

class LinkedinTest extends AbstractHttpControllerTestCase
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
        ->setConstructorArgs([[
            'graphApiVersion' => 'v2.10'
        ]])
        ->getMock();

        $providerMock->method('getResourceOwner')->willReturnCallback(function () {
            return new LinkedInResourceOwner([
                'id'               => 'user_id',
                'firstName'        => 'User',
                'lastName'         => 'Name',
                'publicProfileUrl' => 'http://example.com/user_id',
                'pictureUrl'       => 'http://example.com/user_id.jpg'
            ]);
        });

        $service = $this->getService();

        $service->setProvider($providerMock);
    }

    /**
     * @return Linkedin
     */
    private function getService()
    {
        $manager = $this->getApplicationServiceLocator()->get('ExternalLoginServiceManager');

        $this->assertInstanceOf(PluginManager::class, $manager);

        $service = $manager->get('linked-in');

        $this->assertInstanceOf(Linkedin::class, $service);

        return $service;
    }

    public function testUrls()
    {
        $service = $this->getService();

        $loginUrl = $service->getLoginUrl();

        $this->assertRegExp(
            '|https://www\.linkedin\.com/uas/oauth2/authorization' .
                '\?state=[a-z0-9]+&scope=&response_type=code&approval_prompt=auto' .
                '&redirect_uri=http%3A%2F%2Fexample\.com%2F&client_id=xxxx|iu',
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
        $this->assertEquals('User Name', $data->getName());
        $this->assertEquals('user_id', $data->getExternalId());
        $this->assertEquals('http://example.com/user_id', $data->getProfileUrl());
        $this->assertEquals('http://example.com/user_id.jpg', $data->getPhotoUrl());
    }
}

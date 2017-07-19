<?php

namespace AutowpTest\ExternalLoginService;

use League\OAuth2\Client\Provider\GithubResourceOwner;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

use Autowp\ExternalLoginService\Github;
use Autowp\ExternalLoginService\PluginManager;
use Autowp\ExternalLoginService\Result;
use League\OAuth2\Client\Provider\Exception\GithubIdentityProviderException;

class GithubTest extends AbstractHttpControllerTestCase
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
        $providerMock = $this->getMockBuilder(\League\OAuth2\Client\Provider\Github::class)
            ->setConstructorArgs([[]])
            ->getMock();

        $providerMock->method('getResourceOwner')->willReturnCallback(function () {
            return new GithubResourceOwner([
                'id'         => 'user_id',
                'name'       => 'UserName',
                'html_url'   => 'http://example.com/user_id',
                'avatar_url' => 'http://example.com/user_id.jpg'
            ]);
        });

        $service = $this->getService();

        $service->setProvider($providerMock);
    }

    /**
     * @return Github
     */
    private function getService()
    {
        $manager = $this->getApplicationServiceLocator()->get('ExternalLoginServiceManager');

        $this->assertInstanceOf(PluginManager::class, $manager);

        $service = $manager->get('github');

        $this->assertInstanceOf(Github::class, $service);

        return $service;
    }

    public function testUrls()
    {
        $service = $this->getService();

        $loginUrl = $service->getLoginUrl();

        $this->assertRegExp(
            '|https://github\.com/login/oauth/authorize\?state=[a-z0-9]+&scope=' .
                '&response_type=code&approval_prompt=auto' .
                '&redirect_uri=http%3A%2F%2Fexample\.com%2F&client_id=xxxx|iu',
            $loginUrl
        );
    }

    public function testThrowsCredentialRequired()
    {
        $this->expectException(GithubIdentityProviderException::class);

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
        $this->assertEquals($data->getName(), 'UserName');
        $this->assertEquals($data->getExternalId(), 'user_id');
        $this->assertEquals($data->getProfileUrl(), 'http://example.com/user_id');
        $this->assertEquals($data->getPhotoUrl(), 'http://example.com/user_id.jpg');
    }
}

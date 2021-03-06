<?php

declare(strict_types=1);

namespace AutowpTest\ExternalLoginService;

use Autowp\ExternalLoginService\PluginManager;
use Autowp\ExternalLoginService\Result;
use Autowp\ExternalLoginService\Twitter;
use Exception;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use League\OAuth1\Client;

class TwitterTest extends AbstractHttpControllerTestCase
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
        $serverMock = $this->getMockBuilder(Client\Server\Twitter::class)
            ->onlyMethods(['getUserDetails', 'getTemporaryCredentials', 'getTokenCredentials'])
            ->setConstructorArgs([
                [
                    'identifier'   => 'xxxx',
                    'secret'       => 'yyyy',
                    'callback_uri' => 'http://example.com/',
                ],
            ])
            ->getMock();

        $serverMock->method('getUserDetails')->willReturnCallback(function () {
            $user = new Client\Server\User();

            $user->uid         = 'user_id';
            $user->nickname    = 'user_id';
            $user->name        = 'UserName';
            $user->location    = 'New York';
            $user->description = 'Description';
            $user->imageUrl    = 'http://example.com/user_id.jpg';
            $user->email       = 'email@example.com';

            return $user;
        });

        $serverMock->method('getTemporaryCredentials')->willReturnCallback(function () {
            $temporaryCredentials = new Client\Credentials\TemporaryCredentials();
            $temporaryCredentials->setIdentifier('temporary_identifier');
            $temporaryCredentials->setSecret('temporary_secret');
            return $temporaryCredentials;
        });

        $serverMock->method('getTokenCredentials')->willReturnCallback(function () {
            $tokenCredentials = new Client\Credentials\TokenCredentials();
            $tokenCredentials->setIdentifier('oauth_token');
            $tokenCredentials->setSecret('oauth_token_secret');

            return $tokenCredentials;
        });

        $this->getService()->setServer($serverMock);
    }

    private function getService(): Twitter
    {
        $manager = $this->getApplicationServiceLocator()->get('ExternalLoginServiceManager');

        $this->assertInstanceOf(PluginManager::class, $manager);

        $service = $manager->get('twitter');

        $this->assertInstanceOf(Twitter::class, $service);

        return $service;
    }

    public function testUrl(): void
    {
        $this->mockProvider();

        $service = $this->getService();

        $url = $service->getLoginUrl();

        $this->assertNotEmpty($url);

        $this->assertRegExp(
            '|^https://api\.twitter\.com/oauth/authenticate'
                . '\?oauth_token=temporary_identifier$|iu',
            $url
        );
    }

    public function testFriendsUrl(): void
    {
        $this->mockProvider();

        $service = $this->getService();

        $url = $service->getFriendsUrl();

        $this->assertRegExp(
            '|^https://api\.twitter\.com/oauth/authenticate'
                . '\?oauth_token=temporary_identifier$|iu',
            $url
        );
    }

    public function testGetData(): void
    {
        $this->mockProvider();

        $service = $this->getService();

        $accessToken = new Client\Credentials\TokenCredentials();
        $accessToken->setIdentifier('identifier');
        $accessToken->setSecret('secret');

        $service->setAccessToken($accessToken);

        $data = $service->getData([]);

        $this->assertInstanceOf(Result::class, $data);
        $this->assertEquals('UserName', $data->getName());
        $this->assertEquals('user_id', $data->getExternalId());
        $this->assertEquals('http://twitter.com/user_id', $data->getProfileUrl());
        $this->assertEquals('http://example.com/user_id.jpg', $data->getPhotoUrl());
    }

    public function testCallback(): void
    {
        $this->mockProvider();

        $service = $this->getService();

        $session = $service->getSession();

        $temporaryCredentials = new Client\Credentials\TemporaryCredentials();
        $temporaryCredentials->setIdentifier('temporary_identifier');
        $temporaryCredentials->setSecret('temporary_secret');

        $session->temporaryCredentials = $temporaryCredentials;

        $accessToken = $service->callback([
            'oauth_token'    => 'temporary_identifier',
            'oauth_verifier' => 'zzzz-verifier',
        ]);

        $this->assertEquals('oauth_token', $accessToken->getIdentifier());
        $this->assertEquals('oauth_token_secret', $accessToken->getSecret());
    }
}

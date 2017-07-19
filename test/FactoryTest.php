<?php

namespace AutowpTest\ExternalLoginService;

use PHPUnit\Framework\TestCase;

use Autowp\ExternalLoginService\Factory;
use Autowp\ExternalLoginService\Facebook;
use Autowp\ExternalLoginService\Github;
use Autowp\ExternalLoginService\GooglePlus;
use Autowp\ExternalLoginService\Linkedin;
use Autowp\ExternalLoginService\Twitter;
use Autowp\ExternalLoginService\Vk;

/**
 * @group Autowp_ExternalLoginService
 */
class FactoryTest extends TestCase
{

    public function testServicesExists()
    {
        $factory = new Factory([
            'facebook'    => [],
            'github'      => [],
            'google-plus' => [],
            'linkedin'    => [],
            'twitter'     => [],
            'vk'          => [],
        ]);

        $this->assertInstanceOf(Facebook::class, $factory->getService('facebook', 'facebook', []));

        $this->assertInstanceOf(Github::class, $factory->getService('github', 'github', []));

        $this->assertInstanceOf(GooglePlus::class, $factory->getService('google-plus', 'google-plus', []));

        $this->assertInstanceOf(Linkedin::class, $factory->getService('linkedin', 'linkedin', []));

        $this->assertInstanceOf(Twitter::class, $factory->getService('twitter', 'twitter', []));

        $this->assertInstanceOf(Vk::class, $factory->getService('vk', 'vk', []));
    }
}

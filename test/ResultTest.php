<?php

declare(strict_types=1);

namespace AutowpTest\ExternalLoginService;

use Autowp\ExternalLoginService\ExternalLoginServiceException;
use Autowp\ExternalLoginService\Result;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * @group Autowp_ExternalLoginService
 */
class ResultTest extends TestCase
{
    public function testBasicCorrect(): void
    {
        $data = [
            'externalId' => '123',
            'name'       => 'Ivanov Ivan',
            'profileUrl' => null,
            'photoUrl'   => 'http://google.com/image.png',
            'email'      => 'test@example.com',
            'birthday'   => new DateTime(),
            'location'   => null,
            'gender'     => null,
            'language'   => null,
        ];

        $result = new Result($data);

        $this->assertEquals($data, $result->toArray());
    }

    public function testIncorrectPhotoUriThrowsException(): void
    {
        $this->expectException(ExternalLoginServiceException::class);
        new Result([
            'externalId' => '123',
            'name'       => 'Ivanov Ivan',
            'profileUrl' => null,
            'photoUrl'   => 'google.com/image.png',
            'email'      => 'test@example.com',
            'birthday'   => null,
            'location'   => null,
            'gender'     => null,
            'language'   => null,
        ]);
    }

    public function testIncorrectEmailThrowsException(): void
    {
        $this->expectException(ExternalLoginServiceException::class);
        new Result([
            'externalId' => '123',
            'name'       => 'Ivanov Ivan',
            'profileUrl' => null,
            'photoUrl'   => null,
            'email'      => 'example.com',
            'birthday'   => null,
            'location'   => null,
            'gender'     => null,
            'language'   => null,
        ]);
    }
}

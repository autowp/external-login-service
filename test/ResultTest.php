<?php

namespace AutowpTest\ExternalLoginService;

use Autowp\ExternalLoginService\Result;
use Autowp\ExternalLoginService\InvalidUriException;
use Autowp\ExternalLoginService\InvalidEmailAddressException;

use DateTime;

/**
 * @group Autowp_ExternalLoginService
 */
class ResultTest extends \PHPUnit_Framework_TestCase
{
    
    public function testBasicCorect()
    {
        $data = array(
            'externalId' => '123',
            'name'       => 'Ivanov Ivan',
            'profileUrl' => null,
            'photoUrl'   => 'http://google.com/image.png',
            'email'      => 'test@example.com',
            'birthday'   => new DateTime(),
            'residence'  => null,
            'gender'     => null
        );
    
        $result = new Result($data);
    
        $this->assertEquals($data, $result->toArray());
    }

    public function testIncorrectPhotoUriThrowsException()
    {
        $this->expectException(InvalidUriException::class);
        
        $result = new Result(array(
            'externalId' => '123',
            'name'       => 'Ivanov Ivan',
            'profileUrl' => null,
            'photoUrl'   => 'google.com/image.png',
            'email'      => 'test@example.com',
            'birthday'   => null,
            'residence'  => null,
            'gender'     => null
        ));
    }

    public function testIncorrectEmailThrowsException()
    {
        $this->expectException(InvalidEmailAddressException::class);
    
        $result = new Result(array(
            'externalId' => '123',
            'name'       => 'Ivanov Ivan',
            'profileUrl' => null,
            'photoUrl'   => null,
            'email'      => 'example.com',
            'birthday'   => null,
            'residence'  => null,
            'gender'     => null
        ));
    }
}
<?php

namespace AutowpTest\ExternalLoginService;

use Autowp\ExternalLoginService\Result;

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

    /**
     * @expectedException \Autowp\ExternalLoginService\InvalidUriException
     */
    public function testIncorrectPhotoUriThrowsException()
    {
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

    /**
     * @expectedException \Autowp\ExternalLoginService\InvalidEmailAddressException
     */
    public function testIncorrectEmailThrowsException()
    {    
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
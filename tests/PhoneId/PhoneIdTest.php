<?php

use PhoneId\PhoneId;
use PhoneId\PhoneIdClient;


class PhoneIdTest extends PHPUnit_Framework_TestCase
{

    public function testGetAuthorizeUrl()
    {
        $p = new PhoneId(123, 'secret', array('redirect_uri' => 'http://localhost/return.php'));

        $expected = 'https://login.phone.id/v2/login?client_id=123&redirect_uri=' . urlencode('http://localhost/return.php') . '&response_type=code';
        $actual   = $p->getAuthorizeUrl();
        $this->assertEquals($expected, $actual, 'should build the authorize URL with client_id and redirect_uri');

        $expected = 'https://login.phone.id/v2/login?client_id=123&redirect_uri=' . urlencode('http://www.domain.com/return.php') . '&response_type=code';
        $actual   = $p->getAuthorizeUrl(array('redirect_uri' => 'http://www.domain.com/return.php'));
        $this->assertEquals($expected, $actual, 'should override query string parameters from input');

        $expected = 'https://login.phone.id/v2/login?client_id=123&response_type=code';
        $actual   = $p->getAuthorizeUrl(array('redirect_uri' => null));
        $this->assertEquals($expected, $actual, 'should override and remove null query string parameters');

        $expected = 'https://login.phone.id/v2/login?client_id=123&redirect_uri=' . urlencode('http://localhost/return.php') . '&response_type=code&extra=value';
        $actual   = $p->getAuthorizeUrl(array('extra' => 'value'));
        $this->assertEquals($expected, $actual, 'should append query string parameters from input');

    }

    public function testGetMe()
    {
        $client = $this->getMockBuilder('PhoneIdClient')->setMethods(array('request'))->getMock();
        $client->method('request')->willReturn(array('phone_number' => '+123456789'));
        $client->expects($this->once())->method('request')->with($this->equalTo('GET'), $this->equalTo('/users/me'));

        $p = new PhoneId(123, 'secret', array('client' => $client));
        $this->assertEquals(array('phone_number' => '+123456789'), $p->getMe());
    }

}

<?php

use PhoneId\PhoneId;
use PhoneId\PhoneIdClient;


class PhoneIdTest extends PHPUnit_Framework_TestCase
{

    public function testGetAuthorizeUrl()
    {
        $p = new PhoneId(123, 'secret', array('redirect_uri' => 'http://localhost/return.php'));

        $expected = 'https://api.phone.id/v2/static/login.html?client_id=123&redirect_uri=' . urlencode('http://localhost/return.php');
        $actual   = $p->getAuthorizeUrl();
        $this->assertEquals($expected, $actual, 'should build the authorize URL with client_id and redirect_uri');

        $expected = 'https://api.phone.id/v2/static/login.html?client_id=123&redirect_uri=' . urlencode('http://www.domain.com/return.php');
        $actual   = $p->getAuthorizeUrl(array('redirect_uri' => 'http://www.domain.com/return.php'));
        $this->assertEquals($expected, $actual, 'should override query string parameters from input');

        $expected = 'https://api.phone.id/v2/static/login.html?client_id=123';
        $actual   = $p->getAuthorizeUrl(array('redirect_uri' => null));
        $this->assertEquals($expected, $actual, 'should override and remove null query string parameters');

        $expected = 'https://api.phone.id/v2/static/login.html?client_id=123&redirect_uri=' . urlencode('http://localhost/return.php') . '&extra=value';
        $actual   = $p->getAuthorizeUrl(array('extra' => 'value'));
        $this->assertEquals($expected, $actual, 'should append query string parameters from input');

    }

    public function testGetMe()
    {
        $client = $this->getMockBuilder('PhoneIdClient')->setMethods(array('request'))->getMock();
        $client->method('request')->willReturn(array('phone_number' => '+123456789'));
        $client->expects($this->once())->method('request')->with($this->equalTo('GET'), $this->equalTo('/auth/users/me'));

        $p = new PhoneId(123, 'secret', array('client' => $client));
        $this->assertEquals(array('phone_number' => '+123456789'), $p->getMe());
    }

}

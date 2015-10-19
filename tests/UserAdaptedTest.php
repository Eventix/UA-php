<?php

class UserAdaptedTest extends PHPUnit_Framework_TestCase {

    public function testVersion()
    {
        $ua = new UserAdapted\UserAdapted();
        $this->assertTrue($ua->version() == '0.1');
    }

    public function testApiKeyNotSet()
    {
        $this->setExpectedException('InvalidArgumentException');
        $ua = new UserAdapted\UserAdapted();
        $ua->init();
    }
}
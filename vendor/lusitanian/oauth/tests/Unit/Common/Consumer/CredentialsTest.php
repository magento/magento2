<?php

namespace OAuth\Unit\Common\Consumer;

use OAuth\Common\Consumer\Credentials;

class CredentialsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers OAuth\Common\Consumer\Credentials::__construct
     */
    public function testConstructCorrectInterface()
    {
        $credentials = new Credentials('foo', 'bar', 'baz');

        $this->assertInstanceOf('\\OAuth\\Common\\Consumer\\CredentialsInterface', $credentials);
    }

    /**
     * @covers OAuth\Common\Consumer\Credentials::__construct
     * @covers OAuth\Common\Consumer\Credentials::getConsumerId
     */
    public function testGetConsumerId()
    {
        $credentials = new Credentials('foo', 'bar', 'baz');

        $this->assertSame('foo', $credentials->getConsumerId());
    }

    /**
     * @covers OAuth\Common\Consumer\Credentials::__construct
     * @covers OAuth\Common\Consumer\Credentials::getConsumerSecret
     */
    public function testGetConsumerSecret()
    {
        $credentials = new Credentials('foo', 'bar', 'baz');

        $this->assertSame('bar', $credentials->getConsumerSecret());
    }

    /**
     * @covers OAuth\Common\Consumer\Credentials::__construct
     * @covers OAuth\Common\Consumer\Credentials::getCallbackUrl
     */
    public function testGetCallbackUrl()
    {
        $credentials = new Credentials('foo', 'bar', 'baz');

        $this->assertSame('baz', $credentials->getCallbackUrl());
    }
}

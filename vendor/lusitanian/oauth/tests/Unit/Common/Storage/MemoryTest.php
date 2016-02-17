<?php

namespace OAuthTest\Unit\Common\Storage;

use OAuth\Common\Storage\Memory;

class MemoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers OAuth\Common\Storage\Memory::__construct
     */
    public function testConstructCorrectInterface()
    {
        $storage = new Memory();

        $this->assertInstanceOf('\\OAuth\\Common\\Storage\\TokenStorageInterface', $storage);
    }

    /**
     * @covers OAuth\Common\Storage\Memory::__construct
     * @covers OAuth\Common\Storage\Memory::storeAccessToken
     */
    public function testStoreAccessToken()
    {
        $storage = new Memory();

        $this->assertInstanceOf(
            '\\OAuth\\Common\\Storage\\Memory',
            $storage->storeAccessToken('foo', $this->getMock('\\OAuth\\Common\\Token\\TokenInterface'))
        );
    }

    /**
     * @covers OAuth\Common\Storage\Memory::__construct
     * @covers OAuth\Common\Storage\Memory::storeAccessToken
     * @covers OAuth\Common\Storage\Memory::retrieveAccessToken
     * @covers OAuth\Common\Storage\Memory::hasAccessToken
     */
    public function testRetrieveAccessTokenValid()
    {
        $storage = new Memory();

        $storage->storeAccessToken('foo', $this->getMock('\\OAuth\\Common\\Token\\TokenInterface'));

        $this->assertInstanceOf('\\OAuth\\Common\\Token\\TokenInterface', $storage->retrieveAccessToken('foo'));
    }

    /**
     * @covers OAuth\Common\Storage\Memory::__construct
     * @covers OAuth\Common\Storage\Memory::retrieveAccessToken
     * @covers OAuth\Common\Storage\Memory::hasAccessToken
     */
    public function testRetrieveAccessTokenThrowsExceptionWhenTokenIsNotFound()
    {
        $this->setExpectedException('\\OAuth\\Common\\Storage\\Exception\\TokenNotFoundException');

        $storage = new Memory();

        $storage->retrieveAccessToken('foo');
    }

    /**
     * @covers OAuth\Common\Storage\Memory::__construct
     * @covers OAuth\Common\Storage\Memory::storeAccessToken
     * @covers OAuth\Common\Storage\Memory::hasAccessToken
     */
    public function testHasAccessTokenTrue()
    {
        $storage = new Memory();

        $storage->storeAccessToken('foo', $this->getMock('\\OAuth\\Common\\Token\\TokenInterface'));

        $this->assertTrue($storage->hasAccessToken('foo'));
    }

    /**
     * @covers OAuth\Common\Storage\Memory::__construct
     * @covers OAuth\Common\Storage\Memory::hasAccessToken
     */
    public function testHasAccessTokenFalse()
    {
        $storage = new Memory();

        $this->assertFalse($storage->hasAccessToken('foo'));
    }

    /**
     * @covers OAuth\Common\Storage\Memory::__construct
     * @covers OAuth\Common\Storage\Memory::clearToken
     */
    public function testClearTokenIsNotSet()
    {
        $storage = new Memory();

        $this->assertInstanceOf('\\OAuth\\Common\\Storage\\Memory', $storage->clearToken('foo'));
    }

    /**
     * @covers OAuth\Common\Storage\Memory::__construct
     * @covers OAuth\Common\Storage\Memory::storeAccessToken
     * @covers OAuth\Common\Storage\Memory::clearToken
     */
    public function testClearTokenSet()
    {
        $storage = new Memory();

        $storage->storeAccessToken('foo', $this->getMock('\\OAuth\\Common\\Token\\TokenInterface'));

        $this->assertTrue($storage->hasAccessToken('foo'));
        $this->assertInstanceOf('\\OAuth\\Common\\Storage\\Memory', $storage->clearToken('foo'));
        $this->assertFalse($storage->hasAccessToken('foo'));
    }

    /**
     * @covers OAuth\Common\Storage\Memory::__construct
     * @covers OAuth\Common\Storage\Memory::storeAccessToken
     * @covers OAuth\Common\Storage\Memory::clearAllTokens
     */
    public function testClearAllTokens()
    {
        $storage = new Memory();

        $storage->storeAccessToken('foo', $this->getMock('\\OAuth\\Common\\Token\\TokenInterface'));
        $storage->storeAccessToken('bar', $this->getMock('\\OAuth\\Common\\Token\\TokenInterface'));

        $this->assertTrue($storage->hasAccessToken('foo'));
        $this->assertTrue($storage->hasAccessToken('bar'));
        $this->assertInstanceOf('\\OAuth\\Common\\Storage\\Memory', $storage->clearAllTokens());
        $this->assertFalse($storage->hasAccessToken('foo'));
        $this->assertFalse($storage->hasAccessToken('bar'));
    }
}

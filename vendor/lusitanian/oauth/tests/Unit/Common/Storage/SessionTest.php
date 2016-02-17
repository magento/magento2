<?php

namespace OAuthTest\Unit\Common\Storage;

use OAuth\Common\Storage\Session;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers OAuth\Common\Storage\Session::__construct
     *
     * @runInSeparateProcess
     */
    public function testConstructCorrectInterface()
    {
        $storage = new Session();

        $this->assertInstanceOf('\\OAuth\\Common\\Storage\\TokenStorageInterface', $storage);
    }

    /**
     * @covers OAuth\Common\Storage\Session::__construct
     *
     * @runInSeparateProcess
     */
    public function testConstructWithoutStartingSession()
    {
        session_start();

        $storage = new Session(false);

        $this->assertInstanceOf('\\OAuth\\Common\\Storage\\TokenStorageInterface', $storage);
    }

    /**
     * @covers OAuth\Common\Storage\Session::__construct
     *
     * @runInSeparateProcess
     */
    public function testConstructTryingToStartWhileSessionAlreadyExists()
    {
        session_start();

        $storage = new Session();

        $this->assertInstanceOf('\\OAuth\\Common\\Storage\\TokenStorageInterface', $storage);
    }

    /**
     * @covers OAuth\Common\Storage\Session::__construct
     *
     * @runInSeparateProcess
     */
    public function testConstructWithExistingSessionKey()
    {
        session_start();

        $_SESSION['lusitanian_oauth_token'] = array();

        $storage = new Session();

        $this->assertInstanceOf('\\OAuth\\Common\\Storage\\TokenStorageInterface', $storage);
    }

    /**
     * @covers OAuth\Common\Storage\Session::__construct
     * @covers OAuth\Common\Storage\Session::storeAccessToken
     *
     * @runInSeparateProcess
     */
    public function testStoreAccessTokenIsAlreadyArray()
    {
        $storage = new Session();

        $this->assertInstanceOf(
            '\\OAuth\\Common\\Storage\\Session',
            $storage->storeAccessToken('foo', $this->getMock('\\OAuth\\Common\\Token\\TokenInterface'))
        );
    }

    /**
     * @covers OAuth\Common\Storage\Session::__construct
     * @covers OAuth\Common\Storage\Session::storeAccessToken
     *
     * @runInSeparateProcess
     */
    public function testStoreAccessTokenIsNotArray()
    {
        $storage = new Session();

        $_SESSION['lusitanian_oauth_token'] = 'foo';

        $this->assertInstanceOf(
            '\\OAuth\\Common\\Storage\\Session',
            $storage->storeAccessToken('foo', $this->getMock('\\OAuth\\Common\\Token\\TokenInterface'))
        );
    }

    /**
     * @covers OAuth\Common\Storage\Session::__construct
     * @covers OAuth\Common\Storage\Session::storeAccessToken
     * @covers OAuth\Common\Storage\Session::retrieveAccessToken
     * @covers OAuth\Common\Storage\Session::hasAccessToken
     *
     * @runInSeparateProcess
     */
    public function testRetrieveAccessTokenValid()
    {
        $storage = new Session();

        $storage->storeAccessToken('foo', $this->getMock('\\OAuth\\Common\\Token\\TokenInterface'));

        $this->assertInstanceOf('\\OAuth\\Common\\Token\\TokenInterface', $storage->retrieveAccessToken('foo'));
    }

    /**
     * @covers OAuth\Common\Storage\Session::__construct
     * @covers OAuth\Common\Storage\Session::retrieveAccessToken
     * @covers OAuth\Common\Storage\Session::hasAccessToken
     *
     * @runInSeparateProcess
     */
    public function testRetrieveAccessTokenThrowsExceptionWhenTokenIsNotFound()
    {
        $this->setExpectedException('\\OAuth\\Common\\Storage\\Exception\\TokenNotFoundException');

        $storage = new Session();

        $storage->retrieveAccessToken('foo');
    }

    /**
     * @covers OAuth\Common\Storage\Session::__construct
     * @covers OAuth\Common\Storage\Session::storeAccessToken
     * @covers OAuth\Common\Storage\Session::hasAccessToken
     *
     * @runInSeparateProcess
     */
    public function testHasAccessTokenTrue()
    {
        $storage = new Session();

        $storage->storeAccessToken('foo', $this->getMock('\\OAuth\\Common\\Token\\TokenInterface'));

        $this->assertTrue($storage->hasAccessToken('foo'));
    }

    /**
     * @covers OAuth\Common\Storage\Session::__construct
     * @covers OAuth\Common\Storage\Session::hasAccessToken
     *
     * @runInSeparateProcess
     */
    public function testHasAccessTokenFalse()
    {
        $storage = new Session();

        $this->assertFalse($storage->hasAccessToken('foo'));
    }

    /**
     * @covers OAuth\Common\Storage\Session::__construct
     * @covers OAuth\Common\Storage\Session::clearToken
     *
     * @runInSeparateProcess
     */
    public function testClearTokenIsNotSet()
    {
        $storage = new Session();

        $this->assertInstanceOf('\\OAuth\\Common\\Storage\\Session', $storage->clearToken('foo'));
    }

    /**
     * @covers OAuth\Common\Storage\Session::__construct
     * @covers OAuth\Common\Storage\Session::storeAccessToken
     * @covers OAuth\Common\Storage\Session::clearToken
     *
     * @runInSeparateProcess
     */
    public function testClearTokenSet()
    {
        $storage = new Session();

        $storage->storeAccessToken('foo', $this->getMock('\\OAuth\\Common\\Token\\TokenInterface'));

        $this->assertTrue($storage->hasAccessToken('foo'));
        $this->assertInstanceOf('\\OAuth\\Common\\Storage\\Session', $storage->clearToken('foo'));
        $this->assertFalse($storage->hasAccessToken('foo'));
    }

    /**
     * @covers OAuth\Common\Storage\Session::__construct
     * @covers OAuth\Common\Storage\Session::storeAccessToken
     * @covers OAuth\Common\Storage\Session::clearAllTokens
     *
     * @runInSeparateProcess
     */
    public function testClearAllTokens()
    {
        $storage = new Session();

        $storage->storeAccessToken('foo', $this->getMock('\\OAuth\\Common\\Token\\TokenInterface'));
        $storage->storeAccessToken('bar', $this->getMock('\\OAuth\\Common\\Token\\TokenInterface'));

        $this->assertTrue($storage->hasAccessToken('foo'));
        $this->assertTrue($storage->hasAccessToken('bar'));
        $this->assertInstanceOf('\\OAuth\\Common\\Storage\\Session', $storage->clearAllTokens());
        $this->assertFalse($storage->hasAccessToken('foo'));
        $this->assertFalse($storage->hasAccessToken('bar'));
    }

    /**
     * @covers OAuth\Common\Storage\Session::__construct
     * @covers OAuth\Common\Storage\Session::__destruct
     *
     * @runInSeparateProcess
     */
    public function testDestruct()
    {
        $storage = new Session();

        unset($storage);
    }

    /**
     * @covers OAuth\Common\Storage\Session::storeAccessToken
     * @covers OAuth\Common\Storage\Session::retrieveAccessToken
     *
     * @runInSeparateProcess
     */
    public function testSerializeUnserialize()
    {
        $mock = $this->getMock('\\OAuth\\Common\\Token\\AbstractToken', array('__sleep'));
        $mock->expects($this->once())
            ->method('__sleep')
            ->will($this->returnValue(array('accessToken')));

        $storage = new Session();
        $storage->storeAccessToken('foo', $mock);
        $retrievedToken = $storage->retrieveAccessToken('foo');

        $this->assertInstanceOf('\\OAuth\\Common\\Token\\AbstractToken', $retrievedToken);
    }
}

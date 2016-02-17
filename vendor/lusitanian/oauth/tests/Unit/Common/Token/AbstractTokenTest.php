<?php

namespace OAuthTest\Unit\Common\Token;

use \OAuth\Common\Token\AbstractToken;

class AbstractTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers OAuth\Common\Token\AbstractToken::__construct
     */
    public function testConstructCorrectInterface()
    {
        $token = $this->getMockForAbstractClass('\\OAuth\\Common\\Token\\AbstractToken');

        $this->assertInstanceOf('\\OAuth\\Common\\Token\\TokenInterface', $token);
    }

    /**
     * @covers OAuth\Common\Token\AbstractToken::__construct
     * @covers OAuth\Common\Token\AbstractToken::getAccessToken
     */
    public function testGetAccessTokenNotSet()
    {
        $token = $this->getMockForAbstractClass('\\OAuth\\Common\\Token\\AbstractToken');

        $this->assertNull($token->getAccessToken());
    }

    /**
     * @covers OAuth\Common\Token\AbstractToken::__construct
     * @covers OAuth\Common\Token\AbstractToken::getAccessToken
     */
    public function testGetAccessTokenSet()
    {
        $token = $this->getMockForAbstractClass('\\OAuth\\Common\\Token\\AbstractToken', array('foo'));

        $this->assertSame('foo', $token->getAccessToken());
    }

    /**
     * @covers OAuth\Common\Token\AbstractToken::__construct
     * @covers OAuth\Common\Token\AbstractToken::getAccessToken
     * @covers OAuth\Common\Token\AbstractToken::setAccessToken
     */
    public function testSetAccessToken()
    {
        $token = $this->getMockForAbstractClass('\\OAuth\\Common\\Token\\AbstractToken');

        $token->setAccessToken('foo');

        $this->assertSame('foo', $token->getAccessToken());
    }

    /**
     * @covers OAuth\Common\Token\AbstractToken::__construct
     * @covers OAuth\Common\Token\AbstractToken::getRefreshToken
     */
    public function testGetRefreshToken()
    {
        $token = $this->getMockForAbstractClass('\\OAuth\\Common\\Token\\AbstractToken');

        $this->assertNull($token->getRefreshToken());
    }

    /**
     * @covers OAuth\Common\Token\AbstractToken::__construct
     * @covers OAuth\Common\Token\AbstractToken::getRefreshToken
     */
    public function testGetRefreshTokenSet()
    {
        $token = $this->getMockForAbstractClass('\\OAuth\\Common\\Token\\AbstractToken', array('foo', 'bar'));

        $this->assertSame('bar', $token->getRefreshToken());
    }

    /**
     * @covers OAuth\Common\Token\AbstractToken::__construct
     * @covers OAuth\Common\Token\AbstractToken::getRefreshToken
     * @covers OAuth\Common\Token\AbstractToken::setRefreshToken
     */
    public function testSetRefreshToken()
    {
        $token = $this->getMockForAbstractClass('\\OAuth\\Common\\Token\\AbstractToken');

        $token->setRefreshToken('foo');

        $this->assertSame('foo', $token->getRefreshToken());
    }

    /**
     * @covers OAuth\Common\Token\AbstractToken::__construct
     * @covers OAuth\Common\Token\AbstractToken::getExtraParams
     */
    public function testGetExtraParamsNotSet()
    {
        $token = $this->getMockForAbstractClass('\\OAuth\\Common\\Token\\AbstractToken');

        $this->assertSame(array(), $token->getExtraParams());
    }

    /**
     * @covers OAuth\Common\Token\AbstractToken::__construct
     * @covers OAuth\Common\Token\AbstractToken::getExtraParams
     */
    public function testGetExtraParamsSet()
    {
        $token = $this->getMockForAbstractClass('\\OAuth\\Common\\Token\\AbstractToken', array('foo', 'bar', null, array('foo', 'bar')));

        $this->assertEquals(array('foo', 'bar'), $token->getExtraParams());
    }

    /**
     * @covers OAuth\Common\Token\AbstractToken::__construct
     * @covers OAuth\Common\Token\AbstractToken::setExtraParams
     * @covers OAuth\Common\Token\AbstractToken::getExtraParams
     */
    public function testSetExtraParams()
    {
        $token = $this->getMockForAbstractClass('\\OAuth\\Common\\Token\\AbstractToken');

        $token->setExtraParams(array('foo', 'bar'));

        $this->assertSame(array('foo', 'bar'), $token->getExtraParams());
    }

    /**
     * @covers OAuth\Common\Token\AbstractToken::__construct
     * @covers OAuth\Common\Token\AbstractToken::setLifetime
     * @covers OAuth\Common\Token\AbstractToken::getEndOfLife
     */
    public function testGetEndOfLifeNotSet()
    {
        $token = $this->getMockForAbstractClass('\\OAuth\\Common\\Token\\AbstractToken');

        $this->assertSame(AbstractToken::EOL_UNKNOWN, $token->getEndOfLife());
    }

    /**
     * @covers OAuth\Common\Token\AbstractToken::__construct
     * @covers OAuth\Common\Token\AbstractToken::setLifetime
     * @covers OAuth\Common\Token\AbstractToken::getEndOfLife
     */
    public function testGetEndOfLifeZero()
    {
        $token = $this->getMockForAbstractClass('\\OAuth\\Common\\Token\\AbstractToken', array('foo', 'bar', 0));

        $this->assertSame(AbstractToken::EOL_NEVER_EXPIRES, $token->getEndOfLife());
    }

    /**
     * @covers OAuth\Common\Token\AbstractToken::__construct
     * @covers OAuth\Common\Token\AbstractToken::setLifetime
     * @covers OAuth\Common\Token\AbstractToken::getEndOfLife
     */
    public function testGetEndOfLifeNeverExpires()
    {
        $token = $this->getMockForAbstractClass('\\OAuth\\Common\\Token\\AbstractToken', array('foo', 'bar', AbstractToken::EOL_NEVER_EXPIRES));

        $this->assertSame(AbstractToken::EOL_NEVER_EXPIRES, $token->getEndOfLife());
    }

    /**
     * @covers OAuth\Common\Token\AbstractToken::__construct
     * @covers OAuth\Common\Token\AbstractToken::setLifetime
     * @covers OAuth\Common\Token\AbstractToken::getEndOfLife
     */
    public function testGetEndOfLifeNeverExpiresFiveMinutes()
    {
        $token = $this->getMockForAbstractClass('\\OAuth\\Common\\Token\\AbstractToken', array('foo', 'bar', 5 * 60));

        $this->assertSame(time() + (5*60), $token->getEndOfLife());
    }

    /**
     * @covers OAuth\Common\Token\AbstractToken::__construct
     * @covers OAuth\Common\Token\AbstractToken::setLifetime
     * @covers OAuth\Common\Token\AbstractToken::getEndOfLife
     * @covers OAuth\Common\Token\AbstractToken::setEndOfLife
     */
    public function testSetEndOfLife()
    {
        $token = $this->getMockForAbstractClass('\\OAuth\\Common\\Token\\AbstractToken');

        $token->setEndOfLife(10);

        $this->assertSame(10, $token->getEndOfLife());
    }
}

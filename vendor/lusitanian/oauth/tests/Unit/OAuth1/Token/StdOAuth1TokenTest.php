<?php

namespace OAuthTest\Unit\OAuth1\Token;

use OAuth\OAuth1\Token\StdOAuth1Token;

class StdOAuth1TokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testConstructCorrectInterfaces()
    {
        $token = new StdOAuth1Token();

        $this->assertInstanceOf('\\OAuth\\OAuth1\\Token\\TokenInterface', $token);
        $this->assertInstanceOf('\\OAuth\\Common\\Token\\AbstractToken', $token);
    }

    /**
     * @covers OAuth\OAuth1\Token\StdOAuth1Token::setRequestToken
     */
    public function testSetRequestToken()
    {
        $token = new StdOAuth1Token();

        $this->assertNull($token->setRequestToken('foo'));
    }

    /**
     * @covers OAuth\OAuth1\Token\StdOAuth1Token::setRequestToken
     * @covers OAuth\OAuth1\Token\StdOAuth1Token::getRequestToken
     */
    public function testGetRequestToken()
    {
        $token = new StdOAuth1Token();

        $this->assertNull($token->setRequestToken('foo'));
        $this->assertSame('foo', $token->getRequestToken());
    }

    /**
     * @covers OAuth\OAuth1\Token\StdOAuth1Token::setRequestTokenSecret
     */
    public function testSetRequestTokenSecret()
    {
        $token = new StdOAuth1Token();

        $this->assertNull($token->setRequestTokenSecret('foo'));
    }

    /**
     * @covers OAuth\OAuth1\Token\StdOAuth1Token::setRequestTokenSecret
     * @covers OAuth\OAuth1\Token\StdOAuth1Token::getRequestTokenSecret
     */
    public function testGetRequestTokenSecret()
    {
        $token = new StdOAuth1Token();

        $this->assertNull($token->setRequestTokenSecret('foo'));
        $this->assertSame('foo', $token->getRequestTokenSecret());
    }

    /**
     * @covers OAuth\OAuth1\Token\StdOAuth1Token::setAccessTokenSecret
     */
    public function testSetAccessTokenSecret()
    {
        $token = new StdOAuth1Token();

        $this->assertNull($token->setAccessTokenSecret('foo'));
    }

    /**
     * @covers OAuth\OAuth1\Token\StdOAuth1Token::setAccessTokenSecret
     * @covers OAuth\OAuth1\Token\StdOAuth1Token::getAccessTokenSecret
     */
    public function testGetAccessTokenSecret()
    {
        $token = new StdOAuth1Token();

        $this->assertNull($token->setAccessTokenSecret('foo'));
        $this->assertSame('foo', $token->getAccessTokenSecret());
    }
}

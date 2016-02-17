<?php

/**
 * @category   OAuth
 * @package    Tests
 * @author     David Desberg <david@daviddesberg.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace OAuth\Unit\Common\Storage;

use OAuth\Common\Storage\SymfonySession;
use OAuth\OAuth2\Token\StdOAuth2Token;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class SymfonySessionTest extends \PHPUnit_Framework_TestCase
{
    protected $session;

    protected $storage;

    public function setUp()
    {
        // set it
        $this->session = new Session(new MockArraySessionStorage());
        $this->storage = new SymfonySession($this->session);
    }

    public function tearDown()
    {
        // delete
        $this->storage->getSession()->clear();
        unset($this->storage);
    }

    /**
     * Check that the token survives the constructor
     */
    public function testStorageSurvivesConstructor()
    {
        $service = 'Facebook';
        $token = new StdOAuth2Token('access', 'refresh', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param'));

        // act
        $this->storage->storeAccessToken($service, $token);
        $this->storage = null;
        $this->storage = new SymfonySession($this->session);

        // assert
        $extraParams = $this->storage->retrieveAccessToken($service)->getExtraParams();
        $this->assertEquals('param', $extraParams['extra']);
        $this->assertEquals($token, $this->storage->retrieveAccessToken($service));
    }

    /**
     * Check that the token gets properly stored.
     */
    public function testStorage()
    {
        // arrange
        $service_1 = 'Facebook';
        $service_2 = 'Foursquare';

        $token_1 = new StdOAuth2Token('access_1', 'refresh_1', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param'));
        $token_2 = new StdOAuth2Token('access_2', 'refresh_2', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param'));

        // act
        $this->storage->storeAccessToken($service_1, $token_1);
        $this->storage->storeAccessToken($service_2, $token_2);

        // assert
        $extraParams = $this->storage->retrieveAccessToken($service_1)->getExtraParams();
        $this->assertEquals('param', $extraParams['extra']);
        $this->assertEquals($token_1, $this->storage->retrieveAccessToken($service_1));
        $this->assertEquals($token_2, $this->storage->retrieveAccessToken($service_2));
    }

    /**
     * Test hasAccessToken.
     */
    public function testHasAccessToken()
    {
        // arrange
        $service = 'Facebook';
        $this->storage->clearToken($service);

        // act
        // assert
        $this->assertFalse($this->storage->hasAccessToken($service));
    }

    /**
     * Check that the token gets properly deleted.
     */
    public function testStorageClears()
    {
        // arrange
        $service = 'Facebook';
        $token = new StdOAuth2Token('access', 'refresh', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param'));

        // act
        $this->storage->storeAccessToken($service, $token);
        $this->storage->clearToken($service);

        // assert
        $this->setExpectedException('OAuth\Common\Storage\Exception\TokenNotFoundException');
        $this->storage->retrieveAccessToken($service);
    }
}

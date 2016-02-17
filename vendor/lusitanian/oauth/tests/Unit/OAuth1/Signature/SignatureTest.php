<?php

namespace OAuthTest\Unit\OAuth1\Signature;

use OAuth\OAuth1\Signature\Signature;

class SignatureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers OAuth\OAuth1\Signature\Signature::__construct
     */
    public function testConstructCorrectInterface()
    {
        $signature = new Signature($this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'));

        $this->assertInstanceOf('\\OAuth\\OAuth1\\Signature\\SignatureInterface', $signature);
    }

    /**
     * @covers OAuth\OAuth1\Signature\Signature::__construct
     * @covers OAuth\OAuth1\Signature\Signature::setHashingAlgorithm
     */
    public function testSetHashingAlgorithm()
    {
        $signature = new Signature($this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'));

        $this->assertNull($signature->setHashingAlgorithm('foo'));
    }

    /**
     * @covers OAuth\OAuth1\Signature\Signature::__construct
     * @covers OAuth\OAuth1\Signature\Signature::setTokenSecret
     */
    public function testSetTokenSecret()
    {
        $signature = new Signature($this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'));

        $this->assertNull($signature->setTokenSecret('foo'));
    }

    /**
     * @covers OAuth\OAuth1\Signature\Signature::__construct
     * @covers OAuth\OAuth1\Signature\Signature::setHashingAlgorithm
     * @covers OAuth\OAuth1\Signature\Signature::setTokenSecret
     * @covers OAuth\OAuth1\Signature\Signature::getSignature
     * @covers OAuth\OAuth1\Signature\Signature::buildSignatureDataString
     * @covers OAuth\OAuth1\Signature\Signature::hash
     * @covers OAuth\OAuth1\Signature\Signature::getSigningKey
     */
    public function testGetSignatureBareUri()
    {
        $credentials = $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface');
        $credentials->expects($this->any())
            ->method('getConsumerSecret')
            ->will($this->returnValue('foo'));


        $signature = new Signature($credentials);

        $signature->setHashingAlgorithm('HMAC-SHA1');
        $signature->setTokenSecret('foo');

        $uri = $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface');
        $uri->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue(''));
        $uri->expects($this->any())
            ->method('getScheme')
            ->will($this->returnValue('http'));
        $uri->expects($this->any())
            ->method('getRawAuthority')
            ->will($this->returnValue(''));
        $uri->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/foo'));

        $this->assertSame('uoCpiII/Lg/cPiF0XrU2pj4eGFQ=', $signature->getSignature($uri, array('pee' => 'haa')));
    }

    /**
     * @covers OAuth\OAuth1\Signature\Signature::__construct
     * @covers OAuth\OAuth1\Signature\Signature::setHashingAlgorithm
     * @covers OAuth\OAuth1\Signature\Signature::setTokenSecret
     * @covers OAuth\OAuth1\Signature\Signature::getSignature
     * @covers OAuth\OAuth1\Signature\Signature::buildSignatureDataString
     * @covers OAuth\OAuth1\Signature\Signature::hash
     * @covers OAuth\OAuth1\Signature\Signature::getSigningKey
     */
    public function testGetSignatureWithQueryString()
    {
        $credentials = $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface');
        $credentials->expects($this->any())
            ->method('getConsumerSecret')
            ->will($this->returnValue('foo'));


        $signature = new Signature($credentials);

        $signature->setHashingAlgorithm('HMAC-SHA1');
        $signature->setTokenSecret('foo');

        $uri = $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface');
        $uri->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue('param1=value1'));
        $uri->expects($this->any())
            ->method('getScheme')
            ->will($this->returnValue('http'));
        $uri->expects($this->any())
            ->method('getRawAuthority')
            ->will($this->returnValue(''));
        $uri->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/foo'));

        $this->assertSame('LxtD+WjJBRppIUvEI79iQ7I0hSo=', $signature->getSignature($uri, array('pee' => 'haa')));
    }

    /**
     * @covers OAuth\OAuth1\Signature\Signature::__construct
     * @covers OAuth\OAuth1\Signature\Signature::setHashingAlgorithm
     * @covers OAuth\OAuth1\Signature\Signature::setTokenSecret
     * @covers OAuth\OAuth1\Signature\Signature::getSignature
     * @covers OAuth\OAuth1\Signature\Signature::buildSignatureDataString
     * @covers OAuth\OAuth1\Signature\Signature::hash
     * @covers OAuth\OAuth1\Signature\Signature::getSigningKey
     */
    public function testGetSignatureWithAuthority()
    {
        $credentials = $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface');
        $credentials->expects($this->any())
            ->method('getConsumerSecret')
            ->will($this->returnValue('foo'));


        $signature = new Signature($credentials);

        $signature->setHashingAlgorithm('HMAC-SHA1');
        $signature->setTokenSecret('foo');

        $uri = $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface');
        $uri->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue('param1=value1'));
        $uri->expects($this->any())
            ->method('getScheme')
            ->will($this->returnValue('http'));
        $uri->expects($this->any())
            ->method('getRawAuthority')
            ->will($this->returnValue('peehaa:pass'));
        $uri->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/foo'));

        $this->assertSame('MHvkRndIntLrxiPkjkiCNsMEqv4=', $signature->getSignature($uri, array('pee' => 'haa')));
    }

    /**
     * @covers OAuth\OAuth1\Signature\Signature::__construct
     * @covers OAuth\OAuth1\Signature\Signature::setHashingAlgorithm
     * @covers OAuth\OAuth1\Signature\Signature::setTokenSecret
     * @covers OAuth\OAuth1\Signature\Signature::getSignature
     * @covers OAuth\OAuth1\Signature\Signature::buildSignatureDataString
     * @covers OAuth\OAuth1\Signature\Signature::hash
     * @covers OAuth\OAuth1\Signature\Signature::getSigningKey
     */
    public function testGetSignatureWithBarePathNonExplicitTrailingHostSlash()
    {
        $credentials = $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface');
        $credentials->expects($this->any())
            ->method('getConsumerSecret')
            ->will($this->returnValue('foo'));


        $signature = new Signature($credentials);

        $signature->setHashingAlgorithm('HMAC-SHA1');
        $signature->setTokenSecret('foo');

        $uri = $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface');
        $uri->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue('param1=value1'));
        $uri->expects($this->any())
            ->method('getScheme')
            ->will($this->returnValue('http'));
        $uri->expects($this->any())
            ->method('getRawAuthority')
            ->will($this->returnValue('peehaa:pass'));
        $uri->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/'));
        $uri->expects($this->any())
            ->method('hasExplicitTrailingHostSlash')
            ->will($this->returnValue(false));

        $this->assertSame('iFELDoiI5Oj9ixB3kHzoPvBpq0w=', $signature->getSignature($uri, array('pee' => 'haa')));
    }

    /**
     * @covers OAuth\OAuth1\Signature\Signature::__construct
     * @covers OAuth\OAuth1\Signature\Signature::setHashingAlgorithm
     * @covers OAuth\OAuth1\Signature\Signature::setTokenSecret
     * @covers OAuth\OAuth1\Signature\Signature::getSignature
     * @covers OAuth\OAuth1\Signature\Signature::buildSignatureDataString
     * @covers OAuth\OAuth1\Signature\Signature::hash
     * @covers OAuth\OAuth1\Signature\Signature::getSigningKey
     */
    public function testGetSignatureWithBarePathWithExplicitTrailingHostSlash()
    {
        $credentials = $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface');
        $credentials->expects($this->any())
            ->method('getConsumerSecret')
            ->will($this->returnValue('foo'));


        $signature = new Signature($credentials);

        $signature->setHashingAlgorithm('HMAC-SHA1');
        $signature->setTokenSecret('foo');

        $uri = $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface');
        $uri->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue('param1=value1'));
        $uri->expects($this->any())
            ->method('getScheme')
            ->will($this->returnValue('http'));
        $uri->expects($this->any())
            ->method('getRawAuthority')
            ->will($this->returnValue('peehaa:pass'));
        $uri->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/'));
        $uri->expects($this->any())
            ->method('hasExplicitTrailingHostSlash')
            ->will($this->returnValue(true));

        $this->assertSame('IEhUsArSTLvbQ3QYr0zzn+Rxpjg=', $signature->getSignature($uri, array('pee' => 'haa')));
    }

    /**
     * @covers OAuth\OAuth1\Signature\Signature::__construct
     * @covers OAuth\OAuth1\Signature\Signature::setHashingAlgorithm
     * @covers OAuth\OAuth1\Signature\Signature::setTokenSecret
     * @covers OAuth\OAuth1\Signature\Signature::getSignature
     * @covers OAuth\OAuth1\Signature\Signature::buildSignatureDataString
     * @covers OAuth\OAuth1\Signature\Signature::hash
     * @covers OAuth\OAuth1\Signature\Signature::getSigningKey
     */
    public function testGetSignatureNoTokenSecretSet()
    {
        $credentials = $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface');
        $credentials->expects($this->any())
            ->method('getConsumerSecret')
            ->will($this->returnValue('foo'));


        $signature = new Signature($credentials);

        $signature->setHashingAlgorithm('HMAC-SHA1');

        $uri = $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface');
        $uri->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue('param1=value1'));
        $uri->expects($this->any())
            ->method('getScheme')
            ->will($this->returnValue('http'));
        $uri->expects($this->any())
            ->method('getRawAuthority')
            ->will($this->returnValue('peehaa:pass'));
        $uri->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/'));
        $uri->expects($this->any())
            ->method('hasExplicitTrailingHostSlash')
            ->will($this->returnValue(true));

        $this->assertSame('YMHF7FYmLq7wzGnnHWYtd1VoBBE=', $signature->getSignature($uri, array('pee' => 'haa')));
    }

    /**
     * @covers OAuth\OAuth1\Signature\Signature::__construct
     * @covers OAuth\OAuth1\Signature\Signature::setHashingAlgorithm
     * @covers OAuth\OAuth1\Signature\Signature::setTokenSecret
     * @covers OAuth\OAuth1\Signature\Signature::getSignature
     * @covers OAuth\OAuth1\Signature\Signature::buildSignatureDataString
     * @covers OAuth\OAuth1\Signature\Signature::hash
     * @covers OAuth\OAuth1\Signature\Signature::getSigningKey
     */
    public function testGetSignatureThrowsExceptionOnUnsupportedAlgo()
    {
        $this->setExpectedException('\\OAuth\\OAuth1\\Signature\\Exception\\UnsupportedHashAlgorithmException');

        $credentials = $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface');
        $credentials->expects($this->any())
            ->method('getConsumerSecret')
            ->will($this->returnValue('foo'));


        $signature = new Signature($credentials);

        $signature->setHashingAlgorithm('UnsupportedAlgo');

        $uri = $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface');
        $uri->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue('param1=value1'));
        $uri->expects($this->any())
            ->method('getScheme')
            ->will($this->returnValue('http'));
        $uri->expects($this->any())
            ->method('getRawAuthority')
            ->will($this->returnValue('peehaa:pass'));
        $uri->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/'));
        $uri->expects($this->any())
            ->method('hasExplicitTrailingHostSlash')
            ->will($this->returnValue(true));

        $signature->getSignature($uri, array('pee' => 'haa'));
    }
}

<?php

namespace OAuthTest\Unit\Common\Http\Uri;

use OAuth\Common\Http\Uri\UriFactory;
use OAuth\Common\Http\Uri\Uri;

class UriFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testConstructCorrectInterface()
    {
        $factory = new UriFactory();

        $this->assertInstanceOf('\\OAuth\\Common\\Http\\Uri\\UriFactoryInterface', $factory);
    }

    /**
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromSuperGlobalArray
     * @covers OAuth\Common\Http\Uri\UriFactory::attemptProxyStyleParse
     */
    public function testCreateFromSuperGlobalArrayUsingProxyStyle()
    {
        $factory = new UriFactory();

        $uri = $factory->createFromSuperGlobalArray(array('REQUEST_URI' => 'http://example.com'));

        $this->assertInstanceOf(
            '\\OAuth\\Common\\Http\\Uri\\UriInterface',
            $uri
        );

        $this->assertSame('http://example.com', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromSuperGlobalArray
     * @covers OAuth\Common\Http\Uri\UriFactory::attemptProxyStyleParse
     * @covers OAuth\Common\Http\Uri\UriFactory::detectScheme
     * @covers OAuth\Common\Http\Uri\UriFactory::detectHost
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPort
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPath
     * @covers OAuth\Common\Http\Uri\UriFactory::detectQuery
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromParts
     */
    public function testCreateFromSuperGlobalArrayHttp()
    {
        $factory = new UriFactory();

        $uri = $factory->createFromSuperGlobalArray(array(
            'HTTPS'        => 'off',
            'HTTP_HOST'    => 'example.com',
            'REQUEST_URI'  => '/foo',
            'QUERY_STRING' => 'param1=value1',
        ));

        $this->assertInstanceOf(
            '\\OAuth\\Common\\Http\\Uri\\UriInterface',
            $uri
        );

        $this->assertSame('http://example.com/foo?param1=value1', $uri->getAbsoluteUri());
    }

    /**
     * This looks wonky David. Should the port really fallback to 80 even when supplying https as scheme?
     *
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromSuperGlobalArray
     * @covers OAuth\Common\Http\Uri\UriFactory::attemptProxyStyleParse
     * @covers OAuth\Common\Http\Uri\UriFactory::detectScheme
     * @covers OAuth\Common\Http\Uri\UriFactory::detectHost
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPort
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPath
     * @covers OAuth\Common\Http\Uri\UriFactory::detectQuery
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromParts
     */
    public function testCreateFromSuperGlobalArrayHttps()
    {
        $factory = new UriFactory();

        $uri = $factory->createFromSuperGlobalArray(array(
            'HTTPS'        => 'on',
            'HTTP_HOST'    => 'example.com',
            'REQUEST_URI'  => '/foo',
            'QUERY_STRING' => 'param1=value1',
        ));

        $this->assertInstanceOf(
            '\\OAuth\\Common\\Http\\Uri\\UriInterface',
            $uri
        );

        $this->assertSame('https://example.com:80/foo?param1=value1', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromSuperGlobalArray
     * @covers OAuth\Common\Http\Uri\UriFactory::attemptProxyStyleParse
     * @covers OAuth\Common\Http\Uri\UriFactory::detectScheme
     * @covers OAuth\Common\Http\Uri\UriFactory::detectHost
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPort
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPath
     * @covers OAuth\Common\Http\Uri\UriFactory::detectQuery
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromParts
     */
    public function testCreateFromSuperGlobalArrayPortSupplied()
    {
        $factory = new UriFactory();

        $uri = $factory->createFromSuperGlobalArray(array(
            'HTTP_HOST'    => 'example.com',
            'SERVER_PORT'  => 21,
            'REQUEST_URI'  => '/foo',
            'QUERY_STRING' => 'param1=value1',
        ));

        $this->assertInstanceOf(
            '\\OAuth\\Common\\Http\\Uri\\UriInterface',
            $uri
        );

        $this->assertSame('http://example.com:21/foo?param1=value1', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromSuperGlobalArray
     * @covers OAuth\Common\Http\Uri\UriFactory::attemptProxyStyleParse
     * @covers OAuth\Common\Http\Uri\UriFactory::detectScheme
     * @covers OAuth\Common\Http\Uri\UriFactory::detectHost
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPort
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPath
     * @covers OAuth\Common\Http\Uri\UriFactory::detectQuery
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromParts
     */
    public function testCreateFromSuperGlobalArrayPortNotSet()
    {
        $factory = new UriFactory();

        $uri = $factory->createFromSuperGlobalArray(array(
            'HTTP_HOST'    => 'example.com',
            'REQUEST_URI'  => '/foo',
            'QUERY_STRING' => 'param1=value1',
        ));

        $this->assertInstanceOf(
            '\\OAuth\\Common\\Http\\Uri\\UriInterface',
            $uri
        );

        $this->assertSame('http://example.com/foo?param1=value1', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromSuperGlobalArray
     * @covers OAuth\Common\Http\Uri\UriFactory::attemptProxyStyleParse
     * @covers OAuth\Common\Http\Uri\UriFactory::detectScheme
     * @covers OAuth\Common\Http\Uri\UriFactory::detectHost
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPort
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPath
     * @covers OAuth\Common\Http\Uri\UriFactory::detectQuery
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromParts
     */
    public function testCreateFromSuperGlobalArrayRequestUriSet()
    {
        $factory = new UriFactory();

        $uri = $factory->createFromSuperGlobalArray(array(
            'HTTP_HOST'    => 'example.com',
            'REQUEST_URI'  => '/foo',
            'QUERY_STRING' => 'param1=value1',
        ));

        $this->assertInstanceOf(
            '\\OAuth\\Common\\Http\\Uri\\UriInterface',
            $uri
        );

        $this->assertSame('http://example.com/foo?param1=value1', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromSuperGlobalArray
     * @covers OAuth\Common\Http\Uri\UriFactory::attemptProxyStyleParse
     * @covers OAuth\Common\Http\Uri\UriFactory::detectScheme
     * @covers OAuth\Common\Http\Uri\UriFactory::detectHost
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPort
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPath
     * @covers OAuth\Common\Http\Uri\UriFactory::detectQuery
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromParts
     */
    public function testCreateFromSuperGlobalArrayRedirectUrlSet()
    {
        $factory = new UriFactory();

        $uri = $factory->createFromSuperGlobalArray(array(
            'HTTP_HOST'    => 'example.com',
            'REDIRECT_URL' => '/foo',
            'QUERY_STRING' => 'param1=value1',
        ));

        $this->assertInstanceOf(
            '\\OAuth\\Common\\Http\\Uri\\UriInterface',
            $uri
        );

        $this->assertSame('http://example.com/foo?param1=value1', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromSuperGlobalArray
     * @covers OAuth\Common\Http\Uri\UriFactory::attemptProxyStyleParse
     * @covers OAuth\Common\Http\Uri\UriFactory::detectScheme
     * @covers OAuth\Common\Http\Uri\UriFactory::detectHost
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPort
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPath
     * @covers OAuth\Common\Http\Uri\UriFactory::detectQuery
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromParts
     */
    public function testCreateFromSuperGlobalArrayThrowsExceptionOnDetectingPathMissingIndices()
    {
        $factory = new UriFactory();

        $this->setExpectedException('\\RuntimeException');

        $uri = $factory->createFromSuperGlobalArray(array(
            'HTTP_HOST'    => 'example.com',
            'QUERY_STRING' => 'param1=value1',
        ));
    }

    /**
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromSuperGlobalArray
     * @covers OAuth\Common\Http\Uri\UriFactory::attemptProxyStyleParse
     * @covers OAuth\Common\Http\Uri\UriFactory::detectScheme
     * @covers OAuth\Common\Http\Uri\UriFactory::detectHost
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPort
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPath
     * @covers OAuth\Common\Http\Uri\UriFactory::detectQuery
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromParts
     */
    public function testCreateFromSuperGlobalArrayWithQueryString()
    {
        $factory = new UriFactory();

        $uri = $factory->createFromSuperGlobalArray(array(
            'HTTP_HOST'    => 'example.com',
            'REQUEST_URI' => '/foo?param1=value1',
            'QUERY_STRING' => 'param1=value1',
        ));

        $this->assertInstanceOf(
            '\\OAuth\\Common\\Http\\Uri\\UriInterface',
            $uri
        );

        $this->assertSame('http://example.com/foo?param1=value1', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromSuperGlobalArray
     * @covers OAuth\Common\Http\Uri\UriFactory::attemptProxyStyleParse
     * @covers OAuth\Common\Http\Uri\UriFactory::detectScheme
     * @covers OAuth\Common\Http\Uri\UriFactory::detectHost
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPort
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPath
     * @covers OAuth\Common\Http\Uri\UriFactory::detectQuery
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromParts
     */
    public function testCreateFromSuperGlobalArrayWithoutQueryString()
    {
        $factory = new UriFactory();

        $uri = $factory->createFromSuperGlobalArray(array(
            'HTTP_HOST'    => 'example.com',
            'REQUEST_URI' => '/foo',
        ));

        $this->assertInstanceOf(
            '\\OAuth\\Common\\Http\\Uri\\UriInterface',
            $uri
        );

        $this->assertSame('http://example.com/foo', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromSuperGlobalArray
     * @covers OAuth\Common\Http\Uri\UriFactory::attemptProxyStyleParse
     * @covers OAuth\Common\Http\Uri\UriFactory::detectScheme
     * @covers OAuth\Common\Http\Uri\UriFactory::detectHost
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPort
     * @covers OAuth\Common\Http\Uri\UriFactory::detectPath
     * @covers OAuth\Common\Http\Uri\UriFactory::detectQuery
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromParts
     */
    public function testCreateFromSuperGlobalArrayHostWithColon()
    {
        $factory = new UriFactory();

        $uri = $factory->createFromSuperGlobalArray(array(
            'HTTP_HOST'    => 'example.com:80',
            'REQUEST_URI' => '/foo',
        ));

        $this->assertInstanceOf(
            '\\OAuth\\Common\\Http\\Uri\\UriInterface',
            $uri
        );

        $this->assertSame('http://example.com/foo', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\UriFactory::createFromAbsolute
     */
    public function testCreateFromAbsolute()
    {
        $factory = new UriFactory();

        $uri = $factory->createFromAbsolute('http://example.com');

        $this->assertInstanceOf(
            '\\OAuth\\Common\\Http\\Uri\\UriInterface',
            $uri
        );

        $this->assertSame('http://example.com', $uri->getAbsoluteUri());
    }
}

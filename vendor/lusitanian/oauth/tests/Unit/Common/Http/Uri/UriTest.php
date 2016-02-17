<?php

namespace OAuthTest\Unit\Common\Http\Uri;

use OAuth\Common\Http\Uri\Uri;

class UriTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     */
    public function testConstructCorrectInterfaceWithoutUri()
    {
        $uri = new Uri();

        $this->assertInstanceOf('\\OAuth\\Common\\Http\\Uri\\UriInterface', $uri);
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     */
    public function testConstructThrowsExceptionOnInvalidUri()
    {
        $this->setExpectedException('\\InvalidArgumentException');

        // http://lxr.php.net/xref/PHP_5_4/ext/standard/tests/url/urls.inc#92
        $uri = new Uri('http://@:/');
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     */
    public function testConstructThrowsExceptionOnUriWithoutScheme()
    {
        $this->setExpectedException('\\InvalidArgumentException');

        $uri = new Uri('www.pieterhordijk.com');
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getScheme
     */
    public function testGetScheme()
    {
        $uri = new Uri('http://example.com');

        $this->assertSame('http', $uri->getScheme());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::protectUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::getUserInfo
     */
    public function testGetUserInfo()
    {
        $uri = new Uri('http://peehaa@example.com');

        $this->assertSame('peehaa', $uri->getUserInfo());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::protectUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::getUserInfo
     */
    public function testGetUserInfoWithPass()
    {
        $uri = new Uri('http://peehaa:pass@example.com');

        $this->assertSame('peehaa:********', $uri->getUserInfo());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::protectUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::getRawUserInfo
     */
    public function testGetRawUserInfo()
    {
        $uri = new Uri('http://peehaa@example.com');

        $this->assertSame('peehaa', $uri->getRawUserInfo());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::protectUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::getRawUserInfo
     */
    public function testGetRawUserInfoWithPass()
    {
        $uri = new Uri('http://peehaa:pass@example.com');

        $this->assertSame('peehaa:pass', $uri->getRawUserInfo());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getHost
     */
    public function testGetHost()
    {
        $uri = new Uri('http://example.com');

        $this->assertSame('example.com', $uri->getHost());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getPort
     */
    public function testGetPortImplicitHttp()
    {
        $uri = new Uri('http://example.com');

        $this->assertSame(80, $uri->getPort());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getPort
     */
    public function testGetPortImplicitHttps()
    {
        $uri = new Uri('https://example.com');

        $this->assertSame(443, $uri->getPort());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getPort
     */
    public function testGetPortExplicit()
    {
        $uri = new Uri('http://example.com:21');

        $this->assertSame(21, $uri->getPort());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getPath
     */
    public function testGetPathNotSupplied()
    {
        $uri = new Uri('http://example.com');

        $this->assertSame('/', $uri->getPath());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getPath
     */
    public function testGetPathSlash()
    {
        $uri = new Uri('http://example.com/');

        $this->assertSame('/', $uri->getPath());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getPath
     */
    public function testGetPath()
    {
        $uri = new Uri('http://example.com/foo');

        $this->assertSame('/foo', $uri->getPath());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getQuery
     */
    public function testGetQueryWithParams()
    {
        $uri = new Uri('http://example.com?param1=first&param2=second');

        $this->assertSame('param1=first&param2=second', $uri->getQuery());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getQuery
     */
    public function testGetQueryWithoutParams()
    {
        $uri = new Uri('http://example.com');

        $this->assertSame('', $uri->getQuery());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getFragment
     */
    public function testGetFragmentExists()
    {
        $uri = new Uri('http://example.com#foo');

        $this->assertSame('foo', $uri->getFragment());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getFragment
     */
    public function testGetFragmentNotExists()
    {
        $uri = new Uri('http://example.com');

        $this->assertSame('', $uri->getFragment());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getAuthority
     */
    public function testGetAuthorityWithoutUserInfo()
    {
        $uri = new Uri('http://example.com');

        $this->assertSame('example.com', $uri->getAuthority());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getAuthority
     */
    public function testGetAuthorityWithoutUserInfoWithExplicitPort()
    {
        $uri = new Uri('http://example.com:21');

        $this->assertSame('example.com:21', $uri->getAuthority());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::protectUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::getAuthority
     */
    public function testGetAuthorityWithUsernameWithExplicitPort()
    {
        $uri = new Uri('http://peehaa@example.com:21');

        $this->assertSame('peehaa@example.com:21', $uri->getAuthority());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::protectUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::getAuthority
     */
    public function testGetAuthorityWithUsernameAndPassWithExplicitPort()
    {
        $uri = new Uri('http://peehaa:pass@example.com:21');

        $this->assertSame('peehaa:********@example.com:21', $uri->getAuthority());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::protectUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::getAuthority
     */
    public function testGetAuthorityWithUsernameAndPassWithoutExplicitPort()
    {
        $uri = new Uri('http://peehaa:pass@example.com');

        $this->assertSame('peehaa:********@example.com', $uri->getAuthority());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getRawAuthority
     */
    public function testGetRawAuthorityWithoutUserInfo()
    {
        $uri = new Uri('http://example.com');

        $this->assertSame('example.com', $uri->getRawAuthority());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getRawAuthority
     */
    public function testGetRawAuthorityWithoutUserInfoWithExplicitPort()
    {
        $uri = new Uri('http://example.com:21');

        $this->assertSame('example.com:21', $uri->getRawAuthority());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::protectUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::getRawAuthority
     */
    public function testGetRawAuthorityWithUsernameWithExplicitPort()
    {
        $uri = new Uri('http://peehaa@example.com:21');

        $this->assertSame('peehaa@example.com:21', $uri->getRawAuthority());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::protectUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::getRawAuthority
     */
    public function testGetRawAuthorityWithUsernameAndPassWithExplicitPort()
    {
        $uri = new Uri('http://peehaa:pass@example.com:21');

        $this->assertSame('peehaa:pass@example.com:21', $uri->getRawAuthority());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::protectUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::getRawAuthority
     */
    public function testGetRawAuthorityWithUsernameAndPassWithoutExplicitPort()
    {
        $uri = new Uri('http://peehaa:pass@example.com');

        $this->assertSame('peehaa:pass@example.com', $uri->getRawAuthority());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testGetAbsoluteUriBare()
    {
        $uri = new Uri('http://example.com');

        $this->assertSame('http://example.com', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::protectUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::getRawAuthority
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testGetAbsoluteUriWithAuthority()
    {
        $uri = new Uri('http://peehaa:pass@example.com');

        $this->assertSame('http://peehaa:pass@example.com', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testGetAbsoluteUriWithPath()
    {
        $uri = new Uri('http://example.com/foo');

        $this->assertSame('http://example.com/foo', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testGetAbsoluteUriWithoutPath()
    {
        $uri = new Uri('http://example.com');

        $this->assertSame('http://example.com', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testGetAbsoluteUriWithoutPathExplicitTrailingSlash()
    {
        $uri = new Uri('http://example.com/');

        $this->assertSame('http://example.com/', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testGetAbsoluteUriWithQuery()
    {
        $uri = new Uri('http://example.com?param1=value1');

        $this->assertSame('http://example.com?param1=value1', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testGetAbsoluteUriWithFragment()
    {
        $uri = new Uri('http://example.com#foo');

        $this->assertSame('http://example.com#foo', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getRelativeUri
     */
    public function testGetRelativeUriWithoutPath()
    {
        $uri = new Uri('http://example.com');

        $this->assertSame('', $uri->getRelativeUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getRelativeUri
     */
    public function testGetRelativeUriWithPath()
    {
        $uri = new Uri('http://example.com/foo');

        $this->assertSame('/foo', $uri->getRelativeUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::getRelativeUri
     */
    public function testGetRelativeUriWithExplicitTrailingSlash()
    {
        $uri = new Uri('http://example.com/');

        $this->assertSame('/', $uri->getRelativeUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::__toString
     */
    public function testToStringBare()
    {
        $uri = new Uri('http://example.com');

        $this->assertSame('http://example.com', (string) $uri);
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::protectUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::getRawAuthority
     * @covers OAuth\Common\Http\Uri\Uri::__toString
     */
    public function testToStringWithAuthority()
    {
        $uri = new Uri('http://peehaa:pass@example.com');

        $this->assertSame('http://peehaa:********@example.com', (string) $uri);
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::__toString
     */
    public function testToStringWithPath()
    {
        $uri = new Uri('http://example.com/foo');

        $this->assertSame('http://example.com/foo', (string) $uri);
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::__toString
     */
    public function testToStringWithoutPath()
    {
        $uri = new Uri('http://example.com');

        $this->assertSame('http://example.com', (string) $uri);
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::__toString
     */
    public function testToStringWithoutPathExplicitTrailingSlash()
    {
        $uri = new Uri('http://example.com/');

        $this->assertSame('http://example.com/', (string) $uri);
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::__toString
     */
    public function testToStringWithQuery()
    {
        $uri = new Uri('http://example.com?param1=value1');

        $this->assertSame('http://example.com?param1=value1', (string) $uri);
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::__toString
     */
    public function testToStringWithFragment()
    {
        $uri = new Uri('http://example.com#foo');

        $this->assertSame('http://example.com#foo', (string) $uri);
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setPath
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testSetPathEmpty()
    {
        $uri = new Uri('http://example.com');
        $uri->setPath('');

        $this->assertSame('http://example.com', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setPath
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testSetPathWithPath()
    {
        $uri = new Uri('http://example.com');
        $uri->setPath('/foo');

        $this->assertSame('http://example.com/foo', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setPath
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testSetPathWithOnlySlash()
    {
        $uri = new Uri('http://example.com');
        $uri->setPath('/');

        $this->assertSame('http://example.com/', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setQuery
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testSetQueryEmpty()
    {
        $uri = new Uri('http://example.com');
        $uri->setQuery('');

        $this->assertSame('http://example.com', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setQuery
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testSetQueryFilled()
    {
        $uri = new Uri('http://example.com');
        $uri->setQuery('param1=value1&param2=value2');

        $this->assertSame('http://example.com?param1=value1&param2=value2', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::addToQuery
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testAddToQueryAppend()
    {
        $uri = new Uri('http://example.com?param1=value1');
        $uri->addToQuery('param2', 'value2');

        $this->assertSame('http://example.com?param1=value1&param2=value2', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::addToQuery
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testAddToQueryCreate()
    {
        $uri = new Uri('http://example.com');
        $uri->addToQuery('param1', 'value1');

        $this->assertSame('http://example.com?param1=value1', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setFragment
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testSetFragmentEmpty()
    {
        $uri = new Uri('http://example.com');
        $uri->setFragment('');

        $this->assertSame('http://example.com', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setFragment
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testSetFragmentWithData()
    {
        $uri = new Uri('http://example.com');
        $uri->setFragment('foo');

        $this->assertSame('http://example.com#foo', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setScheme
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testSetSchemeWithEmpty()
    {
        $uri = new Uri('http://example.com');
        $uri->setScheme('');

        $this->assertSame('://example.com', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setScheme
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testSetSchemeWithData()
    {
        $uri = new Uri('http://example.com');
        $uri->setScheme('foo');

        $this->assertSame('foo://example.com', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testSetUserInfoEmpty()
    {
        $uri = new Uri('http://example.com');
        $uri->setUserInfo('');

        $this->assertSame('http://example.com', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::protectUserInfo
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testSetUserInfoWithData()
    {
        $uri = new Uri('http://example.com');
        $uri->setUserInfo('foo:bar');

        $this->assertSame('http://foo:bar@example.com', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setPort
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testSetPortCustom()
    {
        $uri = new Uri('http://example.com');
        $uri->setPort('21');

        $this->assertSame('http://example.com:21', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setPort
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testSetPortHttpImplicit()
    {
        $uri = new Uri('http://example.com');
        $uri->setPort(80);

        $this->assertSame('http://example.com', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setPort
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testSetPortHttpsImplicit()
    {
        $uri = new Uri('https://example.com');
        $uri->setPort(443);

        $this->assertSame('https://example.com', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setPort
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testSetPortHttpExplicit()
    {
        $uri = new Uri('http://example.com');
        $uri->setPort(443);

        $this->assertSame('http://example.com:443', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setPort
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testSetPortHttpsExplicit()
    {
        $uri = new Uri('https://example.com');
        $uri->setPort(80);

        $this->assertSame('https://example.com:80', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::setHost
     * @covers OAuth\Common\Http\Uri\Uri::getAbsoluteUri
     */
    public function testSetHost()
    {
        $uri = new Uri('http://example.com');
        $uri->setHost('pieterhordijk.com');

        $this->assertSame('http://pieterhordijk.com', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::hasExplicitTrailingHostSlash
     */
    public function testHasExplicitTrailingHostSlashTrue()
    {
        $uri = new Uri('http://example.com/');

        $this->assertTrue($uri->hasExplicitTrailingHostSlash());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::hasExplicitTrailingHostSlash
     */
    public function testHasExplicitTrailingHostSlashFalse()
    {
        $uri = new Uri('http://example.com/foo');

        $this->assertFalse($uri->hasExplicitTrailingHostSlash());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::hasExplicitPortSpecified
     */
    public function testHasExplicitPortSpecifiedTrue()
    {
        $uri = new Uri('http://example.com:8080');

        $this->assertTrue($uri->hasExplicitPortSpecified());
    }

    /**
     * @covers OAuth\Common\Http\Uri\Uri::__construct
     * @covers OAuth\Common\Http\Uri\Uri::parseUri
     * @covers OAuth\Common\Http\Uri\Uri::hasExplicitPortSpecified
     */
    public function testHasExplicitPortSpecifiedFalse()
    {
        $uri = new Uri('http://example.com');

        $this->assertFalse($uri->hasExplicitPortSpecified());
    }
}

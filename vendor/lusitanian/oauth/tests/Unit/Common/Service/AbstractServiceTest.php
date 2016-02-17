<?php

namespace OAuthTest\Unit\Common\Service;

use OAuthTest\Mocks\Common\Service\Mock;

class AbstractServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers OAuth\Common\Service\AbstractService::__construct
     */
    public function testConstructCorrectInterface()
    {
        $service = $this->getMockForAbstractClass(
            '\\OAuth\\Common\\Service\\AbstractService',
            array(
                $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
                $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
                $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
            )
        );

        $this->assertInstanceOf('\\OAuth\\Common\\Service\\ServiceInterface', $service);
    }

    /**
     * @covers OAuth\Common\Service\AbstractService::__construct
     * @covers OAuth\Common\Service\AbstractService::getStorage
     */
    public function testGetStorage()
    {
        $service = $this->getMockForAbstractClass(
            '\\OAuth\\Common\\Service\\AbstractService',
            array(
                $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
                $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
                $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
            )
        );

        $this->assertInstanceOf('\\OAuth\\Common\\Storage\\TokenStorageInterface', $service->getStorage());
    }

    /**
     * @covers OAuth\Common\Service\AbstractService::__construct
     * @covers OAuth\Common\Service\AbstractService::service
     */
    public function testService()
    {
        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertSame('Mock', $service->service());
    }

    /**
     * @covers OAuth\Common\Service\AbstractService::__construct
     * @covers OAuth\Common\Service\AbstractService::determineRequestUriFromPath
     */
    public function testDetermineRequestUriFromPathUsingUriObject()
    {
        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertInstanceOf(
            '\\OAuth\\Common\\Http\\Uri\\UriInterface',
            $service->testDetermineRequestUriFromPath($this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface'))
        );
    }

    /**
     * @covers OAuth\Common\Service\AbstractService::__construct
     * @covers OAuth\Common\Service\AbstractService::determineRequestUriFromPath
     */
    public function testDetermineRequestUriFromPathUsingHttpPath()
    {
        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $uri = $service->testDetermineRequestUriFromPath('http://example.com');

        $this->assertInstanceOf('\\OAuth\\Common\\Http\\Uri\\UriInterface', $uri);
        $this->assertSame('http://example.com', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Service\AbstractService::__construct
     * @covers OAuth\Common\Service\AbstractService::determineRequestUriFromPath
     */
    public function testDetermineRequestUriFromPathUsingHttpsPath()
    {
        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $uri = $service->testDetermineRequestUriFromPath('https://example.com');

        $this->assertInstanceOf('\\OAuth\\Common\\Http\\Uri\\UriInterface', $uri);
        $this->assertSame('https://example.com', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Service\AbstractService::__construct
     * @covers OAuth\Common\Service\AbstractService::determineRequestUriFromPath
     */
    public function testDetermineRequestUriFromPathThrowsExceptionOnInvalidUri()
    {
        $this->setExpectedException('\\OAuth\\Common\\Exception\\Exception');

        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $uri = $service->testDetermineRequestUriFromPath('example.com');
    }

    /**
     * @covers OAuth\Common\Service\AbstractService::__construct
     * @covers OAuth\Common\Service\AbstractService::determineRequestUriFromPath
     */
    public function testDetermineRequestUriFromPathWithQueryString()
    {
        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $uri = $service->testDetermineRequestUriFromPath(
            'path?param1=value1',
            new \OAuth\Common\Http\Uri\Uri('https://example.com')
        );

        $this->assertInstanceOf('\\OAuth\\Common\\Http\\Uri\\UriInterface', $uri);
        $this->assertSame('https://example.com/path?param1=value1', $uri->getAbsoluteUri());
    }

    /**
     * @covers OAuth\Common\Service\AbstractService::__construct
     * @covers OAuth\Common\Service\AbstractService::determineRequestUriFromPath
     */
    public function testDetermineRequestUriFromPathWithLeadingSlashInPath()
    {
        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $uri = $service->testDetermineRequestUriFromPath(
            '/path',
            new \OAuth\Common\Http\Uri\Uri('https://example.com')
        );

        $this->assertInstanceOf('\\OAuth\\Common\\Http\\Uri\\UriInterface', $uri);
        $this->assertSame('https://example.com/path', $uri->getAbsoluteUri());
    }
}

<?php

namespace OAuthTest\Unit\Common\Http;

class AbstractClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers OAuth\Common\Http\Client\AbstractClient::__construct
     */
    public function testConstructCorrectInterface()
    {
        $client = $this->getMockForAbstractClass('\\OAuth\\Common\\Http\\Client\\AbstractClient');

        $this->assertInstanceOf('\\OAuth\\Common\\Http\\Client\\ClientInterface', $client);
    }

    /**
     * @covers OAuth\Common\Http\Client\AbstractClient::__construct
     * @covers OAuth\Common\Http\Client\AbstractClient::setMaxRedirects
     */
    public function testSetMaxRedirects()
    {
        $client = $this->getMockForAbstractClass('\\OAuth\\Common\\Http\\Client\\AbstractClient');

        $this->assertInstanceOf('\\OAuth\\Common\\Http\\Client\\AbstractClient', $client->setMaxRedirects(10));
        $this->assertInstanceOf('\\OAuth\\Common\\Http\\Client\\ClientInterface', $client->setMaxRedirects(10));
    }

    /**
     * @covers OAuth\Common\Http\Client\AbstractClient::__construct
     * @covers OAuth\Common\Http\Client\AbstractClient::setTimeout
     */
    public function testSetTimeout()
    {
        $client = $this->getMockForAbstractClass('\\OAuth\\Common\\Http\\Client\\AbstractClient');

        $this->assertInstanceOf('\\OAuth\\Common\\Http\\Client\\AbstractClient', $client->setTimeout(25));
        $this->assertInstanceOf('\\OAuth\\Common\\Http\\Client\\ClientInterface', $client->setTimeout(25));
    }

    /**
     * @covers OAuth\Common\Http\Client\AbstractClient::__construct
     * @covers OAuth\Common\Http\Client\AbstractClient::normalizeHeaders
     */
    public function testNormalizeHeaders()
    {
        $client = $this->getMockForAbstractClass('\\OAuth\\Common\\Http\\Client\\AbstractClient');

        $original = array(
            'lowercasekey' => 'lowercasevalue',
            'UPPERCASEKEY' => 'UPPERCASEVALUE',
            'mIxEdCaSeKey' => 'MiXeDcAsEvAlUe',
            '31i71casekey' => '31i71casevalue',
        );

        $goal = array(
            'lowercasekey' => 'Lowercasekey: lowercasevalue',
            'UPPERCASEKEY' => 'Uppercasekey: UPPERCASEVALUE',
            'mIxEdCaSeKey' => 'Mixedcasekey: MiXeDcAsEvAlUe',
            '31i71casekey' => '31i71casekey: 31i71casevalue',
        );

        $client->normalizeHeaders($original);

        $this->assertSame($goal, $original);
    }
}

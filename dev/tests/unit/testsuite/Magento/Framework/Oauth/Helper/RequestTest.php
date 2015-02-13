<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Oauth\Helper;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test method getRequestUrl
     *
     * @dataProvider  hostsDataProvider
     * @param $url
     * @param $host
     * @param $trimPort
     * @return string
     */
    public function testGetRequestUrl($url, $host)
    {
        $httpRequestMock = $this->getMock(
            'Magento\Framework\App\Request\Http',
            ['getHttpHost', 'getScheme', 'getRequestUri'],
            [],
            '',
            false
        );

        $httpRequestMock->expects($this->any())->method('getHttpHost')->will($this->returnValue($host));
        $httpRequestMock->expects($this->any())->method('getScheme')->will($this->returnValue('http'));
        $httpRequestMock->expects($this->any())->method('getRequestUri')->will($this->returnValue('/'));

        $oauthRequest = new Request();
        $this->assertEquals($url, $oauthRequest->getRequestUrl($httpRequestMock));
    }

    /**
     * Provides hosts info for tests
     *
     * @return array
     */
    public function hostsDataProvider()
    {
        return  [
            'hostWithoutPort' => [
                'url' => 'http://localhost/',
                'host' => 'localhost'
            ],
            'hostWithPort' => [
                'url' => 'http://localhost:81/',
                'host' => 'localhost:81'
            ]
        ];
    }
}

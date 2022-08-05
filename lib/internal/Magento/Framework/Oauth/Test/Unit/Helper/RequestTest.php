<?php
/**
 * Test WebAPI authentication helper.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Oauth\Test\Unit\Helper;

use Magento\Framework\App\Request\Http;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Framework\Oauth\Helper\Request;
use Magento\Framework\Oauth\OauthInputException;
use Magento\Framework\Phrase;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    /** @var Request */
    protected $oauthRequestHelper;

    /** @var \Magento\Framework\App\Response\Http */
    protected $response;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->oauthRequestHelper = new Request();
        $this->response =
            $this->createPartialMock(Response::class, ['setHttpResponseCode']);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->oauthRequestHelper, $this->response);
    }

    /**
     * @param \Exception $exception
     * @param array $expected
     * @return void
     * @dataProvider dataProviderForPrepareErrorResponseTest
     */
    public function testPrepareErrorResponse($exception, $expected)
    {
        $this->response
            ->expects($this->once())
            ->method('setHttpResponseCode')
            ->with($expected[1]);

        $errorResponse = $this->oauthRequestHelper->prepareErrorResponse($exception, $this->response);
        $this->assertEquals(['oauth_problem' => $expected[0]], $errorResponse);
    }

    /**
     * @return array
     */
    public function dataProviderForPrepareErrorResponseTest()
    {
        return [
            [
                new OauthInputException(new Phrase('msg')),
                ['msg', Request::HTTP_BAD_REQUEST],
            ],
            [
                new \Exception('msg'),
                ['internal_error&message=msg', Request::HTTP_INTERNAL_ERROR]
            ],
            [
                new \Exception(),
                [
                    'internal_error&message=empty_message',
                    Request::HTTP_INTERNAL_ERROR
                ]
            ]
        ];
    }

    /**
     * @param string $url
     * @param string $host
     * @return void
     * @dataProvider hostsDataProvider
     */
    public function testGetRequestUrl($url, $host)
    {
        $httpRequestMock = $this->createPartialMock(
            Http::class,
            ['getHttpHost', 'getScheme', 'getRequestUri']
        );

        $httpRequestMock->expects($this->any())->method('getHttpHost')->willReturn($host);
        $httpRequestMock->expects($this->any())->method('getScheme')->willReturn('http');
        $httpRequestMock->expects($this->any())->method('getRequestUri')->willReturn('/');

        $this->assertEquals($url, $this->oauthRequestHelper->getRequestUrl($httpRequestMock));
    }

    /**
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

    /**
     * Test that the OAuth parameters are correctly extracted from the Authorization header.
     *
     * @param $authHeaderValue
     * @param $expectedParams
     * @dataProvider dataProviderForTestPrepareRequestOAuthHeader
     */
    public function testPrepareRequestOAuthHeader($authHeaderValue, $expectedParams)
    {
        $httpRequestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $httpRequestMock->expects($this->once())->method('getScheme')->willReturn('https');
        $httpRequestMock->expects($this->once())->method('getHttpHost')->willReturn('example.com');
        $httpRequestMock->expects($this->once())->method('getRequestUri')->willReturn('/');

        $httpRequestMock->expects($this->any())
            ->method('getHeader')
            ->willReturnCallback(function ($header) use ($authHeaderValue) {
                switch ($header) {
                    case 'Authorization':
                        return $authHeaderValue;
                    case \Zend_Http_Client::CONTENT_TYPE:
                        return \Zend_Http_Client::ENC_URLENCODED;
                    default:
                        return null;
                }
            });

        $this->assertEquals($expectedParams, $this->oauthRequestHelper->prepareRequest($httpRequestMock));
    }

    /**
     * @return array
     */
    public function dataProviderForTestPrepareRequestOAuthHeader()
    {
        return [
            [
                null,
                []
            ],
            [
                '',
                []
            ],
            [
                'OAuth oauth_consumer_key="x",oauth_token="x", Basic d2luZHNvcm0yOldpTmRzb1JTbWlUSDAwMTQ=',
                ['oauth_consumer_key' => 'x', 'oauth_token' => 'x']
            ],
            [
                'Basic d2luZHNvcm0yOldpTmRzb1JTbWlUSDAwMTQ=, OAuth oauth_consumer_key="x",oauth_token="x"',
                ['oauth_consumer_key' => 'x', 'oauth_token' => 'x']
            ],
            [
                'Basic d2luZHNvcm0yOldpTmRzb1JTbWlUSDAwMTQ=, oauth oauth_consumer_key="x", oauth_token="x"',
                ['oauth_consumer_key' => 'x', 'oauth_token' => 'x']
            ],
            [
                'oauth oauth_consumer_key="x", oauth_token="x", Basic d2luZHNvcm0yOldpTmRzb1JTbWlUSDAwMTQ=',
                ['oauth_consumer_key' => 'x', 'oauth_token' => 'x']
            ]
        ];
    }
}

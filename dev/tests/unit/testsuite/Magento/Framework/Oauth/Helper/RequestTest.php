<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Oauth\Helper;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Oauth\Helper\Request */
    protected $oauthRequestHelper;

    protected function setUp()
    {
        $this->oauthRequestHelper = new Request();
    }

    protected function tearDown()
    {
        unset($this->oauthRequestHelper);
    }

    /**
     * @dataProvider dataProviderForPrepareErrorResponseTest
     */
    public function testPrepareErrorResponse($exception, $response, $expected)
    {
        /* @var $response \Zend_Controller_Response_Http */
        $errorResponse = $this->oauthRequestHelper->prepareErrorResponse($exception, $response);
        $this->assertEquals(['oauth_problem' => $expected[0]], $errorResponse);
        $this->assertEquals($expected[1], $response->getHttpResponseCode());
    }

    public function dataProviderForPrepareErrorResponseTest()
    {
        return [
            [
                new \Magento\Framework\Oauth\OauthInputException('msg'),
                new \Zend_Controller_Response_Http(),
                ['msg', \Magento\Framework\Oauth\Helper\Request::HTTP_BAD_REQUEST],
            ],
            [
                new \Exception('msg'),
                new \Zend_Controller_Response_Http(),
                ['internal_error&message=msg', \Magento\Framework\Oauth\Helper\Request::HTTP_INTERNAL_ERROR]
            ],
            [
                new \Exception(),
                new \Zend_Controller_Response_Http(),
                [
                    'internal_error&message=empty_message',
                    \Magento\Framework\Oauth\Helper\Request::HTTP_INTERNAL_ERROR
                ]
            ]
        ];
    }

    /**
     * @dataProvider hostsDataProvider
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

        $this->assertEquals($url, $this->oauthRequestHelper->getRequestUrl($httpRequestMock));
    }

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

<?php
/**
 * Test WebAPI authentication helper.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Helper\Oauth;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Oauth\Helper\Request */
    protected $_oauthHelper;

    /** @var \Magento\Framework\App\Response\Http */
    protected $response;

    protected function setUp()
    {
        $this->_oauthHelper = new \Magento\Framework\Oauth\Helper\Request();
        $this->response = $this->getMock(
            'Magento\Framework\HTTP\PhpEnvironment\Response',
            ['setHttpResponseCode'],
            [],
            '',
            false
        );
    }

    protected function tearDown()
    {
        unset($this->_oauthHelper, $this->response);
    }

    /**
     * @dataProvider dataProviderForPrepareErrorResponseTest
     */
    public function testPrepareErrorResponse($exception, $expected)
    {
        $this->response
            ->expects($this->once())
            ->method('setHttpResponseCode')
            ->with($expected[1]);

        $errorResponse = $this->_oauthHelper->prepareErrorResponse($exception, $this->response);
        $this->assertEquals(['oauth_problem' => $expected[0]], $errorResponse);
    }

    public function dataProviderForPrepareErrorResponseTest()
    {
        return [
            [
                new \Magento\Framework\Oauth\OauthInputException('msg'),
                ['msg', \Magento\Framework\Oauth\Helper\Request::HTTP_BAD_REQUEST],
            ],
            [
                new \Exception('msg'),
                ['internal_error&message=msg', \Magento\Framework\Oauth\Helper\Request::HTTP_INTERNAL_ERROR]
            ],
            [
                new \Exception(),
                [
                    'internal_error&message=empty_message',
                    \Magento\Framework\Oauth\Helper\Request::HTTP_INTERNAL_ERROR
                ]
            ]
        ];
    }
}

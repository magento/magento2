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

    protected function setUp()
    {
        $this->_oauthHelper = new \Magento\Framework\Oauth\Helper\Request();
    }

    protected function tearDown()
    {
        unset($this->_oauthHelper);
    }

    /**
     * @dataProvider dataProviderForPrepareErrorResponseTest
     */
    public function testPrepareErrorResponse($exception, $response, $expected)
    {
        /* @var $response \Zend\Http\PhpEnvironment\Response */
        $errorResponse = $this->_oauthHelper->prepareErrorResponse($exception, $response);
        $this->assertEquals(['oauth_problem' => $expected[0]], $errorResponse);
        $this->assertEquals($expected[1], $response->getHttpResponseCode());
    }

    public function dataProviderForPrepareErrorResponseTest()
    {
        return [
            [
                new \Magento\Framework\Oauth\OauthInputException('msg'),
                new \Zend\Http\PhpEnvironment\Response(),
                ['msg', \Magento\Framework\Oauth\Helper\Request::HTTP_BAD_REQUEST],
            ],
            [
                new \Exception('msg'),
                new \Zend\Http\PhpEnvironment\Response(),
                ['internal_error&message=msg', \Magento\Framework\Oauth\Helper\Request::HTTP_INTERNAL_ERROR]
            ],
            [
                new \Exception(),
                new \Zend\Http\PhpEnvironment\Response(),
                [
                    'internal_error&message=empty_message',
                    \Magento\Framework\Oauth\Helper\Request::HTTP_INTERNAL_ERROR
                ]
            ]
        ];
    }
}

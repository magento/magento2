<?php
/**
 * Test WebAPI authentication helper.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Helper\Oauth;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Oauth\Helper\Request */
    protected $_oauthHelper;

    protected function setUp()
    {
        $this->_oauthHelper = new \Magento\Oauth\Helper\Request();
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
        /* @var $response \Zend_Controller_Response_Http */
        $errorResponse = $this->_oauthHelper->prepareErrorResponse($exception, $response);
        $this->assertEquals(['oauth_problem' => $expected[0]], $errorResponse);
        $this->assertEquals($expected[1], $response->getHttpResponseCode());
    }

    public function dataProviderForPrepareErrorResponseTest()
    {
        return [
            [
                new \Magento\Oauth\Exception('msg', \Magento\Oauth\OauthInterface::ERR_VERSION_REJECTED),
                new \Zend_Controller_Response_Http(),
                ['version_rejected&message=msg', \Magento\Oauth\Helper\Request::HTTP_BAD_REQUEST]
            ],
            [
                new \Magento\Oauth\Exception('msg', 255),
                new \Zend_Controller_Response_Http(),
                ['unknown_problem&code=255&message=msg', \Magento\Oauth\Helper\Request::HTTP_INTERNAL_ERROR]
            ],
            [
                new \Magento\Oauth\Exception('param', \Magento\Oauth\OauthInterface::ERR_PARAMETER_ABSENT),
                new \Zend_Controller_Response_Http(),
                ['parameter_absent&oauth_parameters_absent=param', \Magento\Oauth\Helper\Request::HTTP_BAD_REQUEST]
            ],
            [
                new \Exception('msg'),
                new \Zend_Controller_Response_Http(),
                ['internal_error&message=msg', \Magento\Oauth\Helper\Request::HTTP_INTERNAL_ERROR]
            ],
            [
                new \Exception(),
                new \Zend_Controller_Response_Http(),
                ['internal_error&message=empty_message', \Magento\Oauth\Helper\Request::HTTP_INTERNAL_ERROR]
            ],
        ];
    }
}

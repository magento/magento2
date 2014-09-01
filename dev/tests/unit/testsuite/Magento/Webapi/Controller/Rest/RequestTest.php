<?php
/**
 * Test Webapi Request model.
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
namespace Magento\Webapi\Controller\Rest;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Request mock.
     *
     * @var \Magento\Webapi\Controller\Rest\Request
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Stdlib\CookieManager
     */
    protected $_cookieManagerMock;

    /** @var \Magento\Webapi\Controller\Rest\Request\Deserializer\Factory */
    protected $_deserializerFactory;

    protected function setUp()
    {
        /** Prepare mocks for request constructor arguments. */
        $this->_deserializerFactory = $this->getMockBuilder(
            'Magento\Webapi\Controller\Rest\Request\Deserializer\Factory'
        )->setMethods(
            array('deserialize', 'get')
        )->disableOriginalConstructor()->getMock();
        $areaListMock = $this->getMock('Magento\Framework\App\AreaList', array(), array(), '', false);
        $configScopeMock = $this->getMock('Magento\Framework\Config\ScopeInterface');
        $areaListMock->expects($this->once())->method('getFrontName')->will($this->returnValue('rest'));
        /** Instantiate request. */
        // TODO: Get rid of SUT mocks.
        $this->_cookieManagerMock = $this->getMock('\Magento\Framework\Stdlib\CookieManager');
        $this->_request = $this->getMock(
            'Magento\Webapi\Controller\Rest\Request',
            array('getHeader', 'getMethod', 'isGet', 'isPost', 'isPut', 'isDelete', 'getRawBody'),
            array($areaListMock, $configScopeMock,$this->_cookieManagerMock, $this->_deserializerFactory,)
        );

        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_deserializerFactory);
        unset($this->_request);
        parent::tearDown();
    }

    /**
     * Test for getAcceptTypes() method.
     *
     * @dataProvider providerAcceptType
     * @param string $acceptHeader Value of Accept HTTP header
     * @param array $expectedResult Method call result
     */
    public function testGetAcceptTypes($acceptHeader, $expectedResult)
    {
        $this->_request->expects(
            $this->once()
        )->method(
            'getHeader'
        )->with(
            'Accept'
        )->will(
            $this->returnValue($acceptHeader)
        );
        $this->assertSame($expectedResult, $this->_request->getAcceptTypes());
    }

    /**
     * Test for getBodyParams() method.
     */
    public function testGetBodyParams()
    {
        $params = array('a' => 123, 'b' => 145);
        $this->_prepareSutForGetBodyParamsTest($params);
        $this->assertEquals($params, $this->_request->getBodyParams(), 'Body parameters were retrieved incorrectly.');
    }

    /**
     * Prepare SUT for GetBodyParams() method mock.
     *
     * @param array $params
     */
    protected function _prepareSutForGetBodyParamsTest($params)
    {
        $rawBody = 'rawBody';
        $this->_request->expects($this->once())->method('getRawBody')->will($this->returnValue($rawBody));
        $contentType = 'contentType';
        $this->_request->expects(
            $this->once()
        )->method(
            'getHeader'
        )->with(
            'Content-Type'
        )->will(
            $this->returnValue($contentType)
        );
        $deserializer = $this->getMockBuilder(
            'Magento\Webapi\Controller\Rest\Request\Deserializer\Json'
        )->disableOriginalConstructor()->setMethods(
            array('deserialize')
        )->getMock();
        $deserializer->expects(
            $this->once()
        )->method(
            'deserialize'
        )->with(
            $rawBody
        )->will(
            $this->returnValue($params)
        );
        $this->_deserializerFactory->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $contentType
        )->will(
            $this->returnValue($deserializer)
        );
    }

    /**
     * Test for getContentType() method.
     *
     * @dataProvider providerContentType
     * @param string $contentTypeHeader 'Content-Type' header value
     * @param string $contentType Appropriate content type for header value
     * @param string|boolean $exceptionMessage \Exception message (boolean FALSE if exception is not expected)
     */
    public function testGetContentType($contentTypeHeader, $contentType, $exceptionMessage = false)
    {
        $this->_request->expects(
            $this->once()
        )->method(
            'getHeader'
        )->with(
            'Content-Type'
        )->will(
            $this->returnValue($contentTypeHeader)
        );

        try {
            $this->assertEquals($contentType, $this->_request->getContentType());
        } catch (\Magento\Webapi\Exception $e) {
            if ($exceptionMessage) {
                $this->assertEquals(
                    $exceptionMessage,
                    $e->getMessage(),
                    'Exception message does not match the expected one.'
                );
                return;
            } else {
                $this->fail('Exception is thrown on valid header: ' . $e->getMessage());
            }
        }
        if ($exceptionMessage) {
            $this->fail('Expected exception was not raised.');
        }
    }

    /**
     * Data provider for testGetAcceptTypes().
     *
     * @return array
     */
    public function providerAcceptType()
    {
        return array(
            // Each element is: array(Accept HTTP header value, expected result))
            array('', array()),
            array(
                'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                array('text/html', 'application/xhtml+xml', 'application/xml', '*/*')
            ),
            array('text/html, application/*, text, */*', array('text/html', 'application/*', 'text', '*/*')),
            array(
                'text/html, application/xml;q=0.9, application/xhtml+xml, image/png, image/webp,' .
                ' image/jpeg, image/gif, image/x-xbitmap, */*;q=0.1',
                array(
                    'text/html',
                    'application/xhtml+xml',
                    'image/png',
                    'image/webp',
                    'image/jpeg',
                    'image/gif',
                    'image/x-xbitmap',
                    'application/xml',
                    '*/*'
                )
            )
        );
    }

    /**
     * Data provider for testGetContentType().
     *
     * @return array
     */
    public function providerContentType()
    {
        return array(
            // Each element is: array(Content-Type header value, content-type part[, expected exception message])
            array('', null, 'Content-Type header is empty.'),
            array('_?', null, 'Content-Type header is invalid.'),
            array('application/x-www-form-urlencoded; charset=UTF-8', 'application/x-www-form-urlencoded'),
            array('application/x-www-form-urlencoded; charset=utf-8', 'application/x-www-form-urlencoded'),
            array('text/html; charset=uTf-8', 'text/html'),
            array('text/html; charset=', null, 'Content-Type header is invalid.'),
            array('text/html;', null, 'Content-Type header is invalid.'),
            array('application/dialog.dot-info7+xml', 'application/dialog.dot-info7+xml'),
            array('application/x-www-form-urlencoded; charset=cp1251', null, 'UTF-8 is the only supported charset.')
        );
    }
}

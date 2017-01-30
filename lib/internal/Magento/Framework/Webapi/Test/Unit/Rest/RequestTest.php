<?php
/**
 * Test Webapi Request model.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Test\Unit\Rest;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Request mock.
     *
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManagerMock;

    /** @var \Magento\Framework\Webapi\Rest\Request\DeserializerFactory */
    protected $_deserializerFactory;

    protected function setUp()
    {
        /** Prepare mocks for request constructor arguments. */
        $this->_deserializerFactory = $this->getMockBuilder(
            'Magento\Framework\Webapi\Rest\Request\DeserializerFactory'
        )->setMethods(
            ['deserialize', 'get']
        )->disableOriginalConstructor()->getMock();
        $areaListMock = $this->getMock('Magento\Framework\App\AreaList', [], [], '', false);
        $configScopeMock = $this->getMock('Magento\Framework\Config\ScopeInterface');
        $areaListMock->expects($this->once())->method('getFrontName')->will($this->returnValue('rest'));
        /** Instantiate request. */
        // TODO: Get rid of SUT mocks.
        $this->_cookieManagerMock = $this->getMock('Magento\Framework\Stdlib\CookieManagerInterface');
        $converterMock = $this->getMockBuilder('Magento\Framework\Stdlib\StringUtils')
            ->disableOriginalConstructor()
            ->setMethods(['cleanString'])
            ->getMock();
        $this->_request = $this->getMock(
            'Magento\Framework\Webapi\Rest\Request',
            ['getHeader', 'getMethod', 'isGet', 'isPost', 'isPut', 'isDelete', 'getContent'],
            [$this->_cookieManagerMock, $converterMock, $areaListMock, $configScopeMock, $this->_deserializerFactory]
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
        $params = ['a' => 123, 'b' => 145];
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
        $content = 'rawBody';
        $this->_request->expects($this->exactly(2))->method('getContent')->will($this->returnValue($content));
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
            'Magento\Framework\Webapi\Rest\Request\Deserializer\Json'
        )->disableOriginalConstructor()->setMethods(
            ['deserialize']
        )->getMock();
        $deserializer->expects(
            $this->once()
        )->method(
            'deserialize'
        )->with(
            $content
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
        } catch (\Magento\Framework\Exception\InputException $e) {
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
        return [
            // Each element is: array(Accept HTTP header value, expected result))
            ['', ['*/*']],
            [
                'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ['text/html', 'application/xhtml+xml', 'application/xml', '*/*']
            ],
            ['text/html, application/*, text, */*', ['text/html', 'application/*', 'text', '*/*']],
            [
                'text/html, application/xml;q=0.9, application/xhtml+xml, image/png, image/webp,' .
                ' image/jpeg, image/gif, image/x-xbitmap, */*;q=0.1',
                [
                    'text/html',
                    'application/xhtml+xml',
                    'image/png',
                    'image/webp',
                    'image/jpeg',
                    'image/gif',
                    'image/x-xbitmap',
                    'application/xml',
                    '*/*'
                ]
            ]
        ];
    }

    /**
     * Data provider for testGetContentType().
     *
     * @return array
     */
    public function providerContentType()
    {
        return [
            // Each element is: array(Content-Type header value, content-type part[, expected exception message])
            ['', null, 'Content-Type header is empty.'],
            ['_?', null, 'Content-Type header is invalid.'],
            ['application/x-www-form-urlencoded; charset=UTF-8', 'application/x-www-form-urlencoded'],
            ['application/x-www-form-urlencoded; charset=utf-8', 'application/x-www-form-urlencoded'],
            ['text/html; charset=uTf-8', 'text/html'],
            ['text/html; charset=', null, 'Content-Type header is invalid.'],
            ['text/html;', null, 'Content-Type header is invalid.'],
            ['application/dialog.dot-info7+xml', 'application/dialog.dot-info7+xml'],
            ['application/x-www-form-urlencoded; charset=cp1251', null, 'UTF-8 is the only supported charset.']
        ];
    }
}

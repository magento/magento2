<?php
/**
 * Test Webapi Request model.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Test\Unit\Rest;

class RequestTest extends \PHPUnit\Framework\TestCase
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

    protected function setUp(): void
    {
        /** Prepare mocks for request constructor arguments. */
        $this->_deserializerFactory = $this->getMockBuilder(
            \Magento\Framework\Webapi\Rest\Request\DeserializerFactory::class
        )->setMethods(
            ['deserialize', 'get']
        )->disableOriginalConstructor()->getMock();
        $areaListMock = $this->createMock(\Magento\Framework\App\AreaList::class);
        $configScopeMock = $this->createMock(\Magento\Framework\Config\ScopeInterface::class);
        $areaListMock->expects($this->once())->method('getFrontName')->willReturn('rest');
        /** Instantiate request. */
        // TODO: Get rid of SUT mocks.
        $this->_cookieManagerMock = $this->createMock(\Magento\Framework\Stdlib\CookieManagerInterface::class);
        $converterMock = $this->getMockBuilder(\Magento\Framework\Stdlib\StringUtils::class)
            ->disableOriginalConstructor()
            ->setMethods(['cleanString'])
            ->getMock();
        $this->_request = $this->getMockBuilder(\Magento\Framework\Webapi\Rest\Request::class)
            ->setMethods(['getHeader', 'getMethod', 'isGet', 'isPost', 'isPut', 'isDelete', 'getContent'])
            ->setConstructorArgs(
                [
                    $this->_cookieManagerMock,
                    $converterMock,
                    $areaListMock,
                    $configScopeMock,
                    $this->_deserializerFactory
                ]
            )
            ->getMock();

        parent::setUp();
    }

    protected function tearDown(): void
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
        )->willReturn(
            $acceptHeader
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
        $this->_request->expects($this->exactly(2))->method('getContent')->willReturn($content);
        $contentType = 'contentType';
        $this->_request->expects(
            $this->once()
        )->method(
            'getHeader'
        )->with(
            'Content-Type'
        )->willReturn(
            $contentType
        );
        $deserializer = $this->getMockBuilder(
            \Magento\Framework\Webapi\Rest\Request\Deserializer\Json::class
        )->disableOriginalConstructor()->setMethods(
            ['deserialize']
        )->getMock();
        $deserializer->expects(
            $this->once()
        )->method(
            'deserialize'
        )->with(
            $content
        )->willReturn(
            $params
        );
        $this->_deserializerFactory->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $contentType
        )->willReturn(
            $deserializer
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
        )->willReturn(
            $contentTypeHeader
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

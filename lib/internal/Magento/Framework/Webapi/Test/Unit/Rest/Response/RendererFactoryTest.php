<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\Rest\Response;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Rest\Response\Renderer\Json;
use Magento\Framework\Webapi\Rest\Response\RendererFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test Rest renderer factory class.
 */
class RendererFactoryTest extends TestCase
{
    /** @var RendererFactory */
    protected $_factory;

    /** @var MockObject */
    protected $_requestMock;

    /** @var MockObject */
    protected $_objectManagerMock;

    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_requestMock = $this->getMockBuilder(
            Request::class
        )->disableOriginalConstructor()
        ->getMock();

        $renders = [
            'default' => ['type' => '*/*', 'model' => Json::class],
            'application_json' => [
                'type' => 'application/json',
                'model' => Json::class,
            ],
        ];

        $this->_factory = new RendererFactory(
            $this->_objectManagerMock,
            $this->_requestMock,
            $renders
        );
    }

    /**
     * Test GET method.
     */
    public function testGet()
    {
        /** Mock request getAcceptTypes method to return specified value. */
        $this->_requestMock->expects($this->once())->method('getHeader')->willReturn('application/json');
        /** Mock renderer. */
        $rendererMock = $this->getMockBuilder(
            Json::class
        )->disableOriginalConstructor()
        ->getMock();
        /** Mock object to return mocked renderer. */
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            Json::class
        )->willReturn(
            $rendererMock
        );
        $this->_factory->get();
    }

    /**
     * Test GET method with wrong Accept HTTP Header.
     */
    public function testGetWithWrongAcceptHttpHeader()
    {
        /** Mock request to return invalid Accept Types. */
        $this->_requestMock->expects($this->once())->method('getHeader')->willReturn('invalid');
        try {
            $this->_factory->get();
            $this->fail("Exception is expected to be raised");
        } catch (Exception $e) {
            $exceptionMessage = 'Server cannot match any of the given Accept HTTP header media type(s) ' .
                'from the request: "invalid" with media types from the config of response renderer.';
            $this->assertInstanceOf(Exception::class, $e, 'Exception type is invalid');
            $this->assertEquals($exceptionMessage, $e->getMessage(), 'Exception message is invalid');
            $this->assertEquals(
                Exception::HTTP_NOT_ACCEPTABLE,
                $e->getHttpCode(),
                'HTTP code is invalid'
            );
        }
    }

    /**
     * Test GET method with wrong Renderer class.
     */
    public function testGetWithWrongRendererClass()
    {
        /** Mock request getAcceptTypes method to return specified value. */
        $this->_requestMock->expects($this->once())->method('getHeader')->willReturn('application/json');
        /** Mock object to return \Magento\Framework\DataObject */
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            Json::class
        )->willReturn(
            new DataObject()
        );

        $this->expectException('LogicException');
        $this->expectExceptionMessage(
            'The renderer must implement "Magento\Framework\Webapi\Rest\Response\RendererInterface".'
        );
        $this->_factory->get();
    }
}

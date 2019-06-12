<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
namespace Magento\Analytics\Test\Unit\Model\Connector\Http;

use Magento\Analytics\Model\Connector\Http\JsonConverter;
use Magento\Analytics\Model\Connector\Http\ResponseHandlerInterface;
use Magento\Analytics\Model\Connector\Http\ResponseResolver;

/**
 * Class ResponseResolverTest
 */
class ResponseResolverTest extends \PHPUnit\Framework\TestCase
{
    public function testGetResultHandleResponseSuccess()
    {
        $expectedBody = ['test' => 'testValue'];
        $response = new \Zend_Http_Response(201, [], json_encode($expectedBody));
        $responseHandlerMock = $this->getMockBuilder(ResponseHandlerInterface::class)
            ->getMockForAbstractClass();
        $responseHandlerMock->expects($this->once())
            ->method('handleResponse')
            ->with($expectedBody)
            ->willReturn(true);
        $notFoundResponseHandlerMock = $this->getMockBuilder(ResponseHandlerInterface::class)
            ->getMockForAbstractClass();
        $notFoundResponseHandlerMock->expects($this->never())->method('handleResponse');
        $responseResolver = new ResponseResolver(
            new JsonConverter(),
            [
                201 => $responseHandlerMock,
                404 => $notFoundResponseHandlerMock,
            ]
        );
        $this->assertTrue($responseResolver->getResult($response));
=======
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\Connector\Http;

use Magento\Analytics\Model\Connector\Http\ConverterInterface;
use Magento\Analytics\Model\Connector\Http\ResponseHandlerInterface;
use Magento\Analytics\Model\Connector\Http\ResponseResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ResponseResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converterMock;

    /**
     * @var ResponseHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $successResponseHandlerMock;

    /**
     * @var ResponseHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notFoundResponseHandlerMock;

    /**
     * @var ResponseResolver
     */
    private $responseResolver;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->converterMock = $this->getMockBuilder(ConverterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->successResponseHandlerMock = $this->getMockBuilder(ResponseHandlerInterface::class)
            ->getMockForAbstractClass();
        $this->notFoundResponseHandlerMock = $this->getMockBuilder(ResponseHandlerInterface::class)
            ->getMockForAbstractClass();
        $this->responseResolver = $this->objectManagerHelper->getObject(
            ResponseResolver::class,
            [
                'converter' => $this->converterMock,
                'responseHandlers' => [
                    201 => $this->successResponseHandlerMock,
                    404 => $this->notFoundResponseHandlerMock,
                ]
            ]
        );
    }

    /**
     * @return void
     * @throws \Zend_Http_Exception
     */
    public function testGetResultHandleResponseSuccess()
    {
        $expectedBody = ['test' => 'testValue'];
        $response = new \Zend_Http_Response(201, ['Content-Type' => 'application/json'], json_encode($expectedBody));
        $this->converterMock
            ->method('getContentMediaType')
            ->willReturn('application/json');

        $this->successResponseHandlerMock
            ->expects($this->once())
            ->method('handleResponse')
            ->with($expectedBody)
            ->willReturn(true);
        $this->notFoundResponseHandlerMock
            ->expects($this->never())
            ->method('handleResponse');
        $this->converterMock
            ->method('fromBody')
            ->willReturn($expectedBody);
        $this->assertTrue($this->responseResolver->getResult($response));
    }

    /**
     * @return void
     * @throws \Zend_Http_Exception
     */
    public function testGetResultHandleResponseUnexpectedContentType()
    {
        $expectedBody = 'testString';
        $response = new \Zend_Http_Response(201, ['Content-Type' => 'plain/text'], $expectedBody);
        $this->converterMock
            ->method('getContentMediaType')
            ->willReturn('application/json');
        $this->converterMock
            ->expects($this->never())
            ->method('fromBody');
        $this->successResponseHandlerMock
            ->expects($this->once())
            ->method('handleResponse')
            ->with([])
            ->willReturn(false);
        $this->notFoundResponseHandlerMock
            ->expects($this->never())
            ->method('handleResponse');
        $this->assertFalse($this->responseResolver->getResult($response));
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }
}

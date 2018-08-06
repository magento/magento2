<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReleaseNotification\Test\Unit\Model\Connector\Http;

use Magento\ReleaseNotification\Model\Connector\ResponseHandlerInterface;
use Magento\ReleaseNotification\Model\Connector\Http\ResponseResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ResponseResolverTest
 */
class ResponseResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResponseResolver
     */
    private $responseResolver;

    /**
     * @var ResponseHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseHandlerMock;

    /**
     * @var ResponseHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notFoundResponseHandlerMock;

    public function setUp()
    {
        $this->responseHandlerMock = $this->getMockBuilder(ResponseHandlerInterface::class)
            ->getMockForAbstractClass();
        $this->notFoundResponseHandlerMock = $this->getMockBuilder(ResponseHandlerInterface::class)
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->responseResolver = $objectManager->getObject(
            ResponseResolver::class,
            [
                'responseHandlers' => [
                    200 => $this->responseHandlerMock,
                    404 => $this->notFoundResponseHandlerMock
                ]
            ]
        );
    }

    public function testGetResult()
    {
        $response = ['test' => 'testValue'];
        $status = 200;

        $this->responseHandlerMock->expects($this->once())
            ->method('handleResponse')
            ->with($response)
            ->willReturn(true);
        $this->notFoundResponseHandlerMock->expects($this->never())->method('handleResponse');

        $this->assertTrue($this->responseResolver->getResult($response, $status));
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Controller\Sidebar;

use Exception;
use Magento\Checkout\Controller\Sidebar\UpdateItemQty;
use Magento\Checkout\Model\Sidebar;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UpdateItemQtyTest extends TestCase
{
    /**
     * @var UpdateItemQty
     */
    private $action;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ResultJsonFactory|MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var Sidebar|MockObject
     */
    private $sidebarMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    protected function setUp()
    {
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->resultJsonFactoryMock = $this->createPartialMock(
            ResultJsonFactory::class,
            ['create']
        );
        $this->sidebarMock = $this->createMock(Sidebar::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $objectManager = new ObjectManager($this);
        $this->action = $objectManager->getObject(
            UpdateItemQty::class,
            [
                'request' => $this->requestMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'sidebar' => $this->sidebarMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    public function testExecute()
    {
        $responseData = [
            'data' => [
                'summary_qty' => 2,
                'summary_text' => __(' items'),
                'subtotal' => 12.34,
            ],
        ];

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('item_id', null)
            ->willReturn('1');
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('item_qty', null)
            ->willReturn('2');

        $this->sidebarMock->expects($this->once())
            ->method('checkQuoteItem')
            ->with(1)
            ->willReturnSelf();
        $this->sidebarMock->expects($this->once())
            ->method('updateQuoteItem')
            ->with(1, 2)
            ->willReturnSelf();
        $this->sidebarMock->expects($this->once())
            ->method('getResponseData')
            ->with('')
            ->willReturn($responseData);

        $resultJson = $this->createMock(ResultJson::class);
        $resultJson->expects($this->once())
            ->method('setData')
            ->with($responseData)
            ->willReturnSelf();
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJson);

        $this->assertSame($resultJson, $this->action->execute());
    }

    public function testExecuteWithLocalizedException()
    {
        $errorMessage = 'Error!';
        $responseData = [
            'success' => false,
            'error_message' => $errorMessage
        ];

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('item_id', null)
            ->willReturn('1');
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('item_qty', null)
            ->willReturn('2');

        $this->sidebarMock->expects($this->once())
            ->method('checkQuoteItem')
            ->with(1)
            ->willThrowException(new LocalizedException(__($errorMessage)));

        $this->sidebarMock->expects($this->once())
            ->method('getResponseData')
            ->with($errorMessage)
            ->willReturn($responseData);

        $resultJson = $this->createMock(ResultJson::class);
        $resultJson->expects($this->once())
            ->method('setData')
            ->with($responseData)
            ->willReturnSelf();
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJson);

        $this->assertSame($resultJson, $this->action->execute());
    }

    public function testExecuteWithException()
    {
        $errorMessage = 'Error!';
        $responseData = [
            'success' => false,
            'error_message' => $errorMessage
        ];

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('item_id', null)
            ->willReturn('1');
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('item_qty', null)
            ->willReturn('2');

        $exception = new Exception($errorMessage);

        $this->sidebarMock->expects($this->once())
            ->method('checkQuoteItem')
            ->with(1)
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception)
            ->willReturn(null);

        $this->sidebarMock->expects($this->once())
            ->method('getResponseData')
            ->with($errorMessage)
            ->willReturn($responseData);

        $resultJson = $this->createMock(ResultJson::class);
        $resultJson->expects($this->once())
            ->method('setData')
            ->with($responseData)
            ->willReturnSelf();
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJson);

        $this->assertSame($resultJson, $this->action->execute());
    }
}

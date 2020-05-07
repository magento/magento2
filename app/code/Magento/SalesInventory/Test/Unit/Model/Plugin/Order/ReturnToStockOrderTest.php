<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesInventory\Test\Unit\Model\Plugin\Order;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsExtensionInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\RefundOrderInterface;
use Magento\SalesInventory\Model\Order\ReturnProcessor;
use Magento\SalesInventory\Model\Plugin\Order\ReturnToStockOrder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReturnToStockOrderTest extends TestCase
{
    /** @var  ReturnToStockOrder */
    private $returnTOStock;

    /**
     * @var MockObject|ReturnProcessor
     */
    private $returnProcessorMock;

    /**
     * @var MockObject|CreditmemoRepositoryInterface
     */
    private $creditmemoRepositoryMock;

    /**
     * @var MockObject|OrderRepositoryInterface
     */
    private $orderRepositoryMock;

    /**
     * @var MockObject|RefundOrderInterface
     */
    private $refundOrderMock;

    /**
     * @var MockObject|CreditmemoCreationArgumentsInterface
     */
    private $creditmemoCreationArgumentsMock;

    /**
     * @var MockObject|OrderInterface
     */
    private $orderMock;

    /**
     * @var MockObject|CreditmemoInterface
     */
    private $creditmemoMock;

    /**
     * @var MockObject|StockConfigurationInterface
     */
    private $stockConfigurationMock;

    /**
     * @var MockObject|CreditmemoCreationArgumentsInterface
     */
    private $extensionAttributesMock;

    protected function setUp(): void
    {
        $this->returnProcessorMock = $this->getMockBuilder(ReturnProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoRepositoryMock = $this->getMockBuilder(CreditmemoRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->refundOrderMock = $this->getMockBuilder(RefundOrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->creditmemoCreationArgumentsMock = $this->getMockBuilder(CreditmemoCreationArgumentsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->extensionAttributesMock = $this->getMockBuilder(CreditmemoCreationArgumentsExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getReturnToStockItems'])
            ->getMockForAbstractClass();
        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->creditmemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->stockConfigurationMock = $this->getMockBuilder(StockConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->returnTOStock = new ReturnToStockOrder(
            $this->returnProcessorMock,
            $this->creditmemoRepositoryMock,
            $this->orderRepositoryMock,
            $this->stockConfigurationMock
        );
    }

    public function testAfterExecute()
    {
        $orderId = 1;
        $creditmemoId = 99;
        $items = [];
        $returnToStockItems = [1];
        $this->creditmemoCreationArgumentsMock->expects($this->exactly(3))
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);

        $this->extensionAttributesMock->expects($this->exactly(2))
            ->method('getReturnToStockItems')
            ->willReturn($returnToStockItems);

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($this->orderMock);

        $this->creditmemoRepositoryMock->expects($this->once())
            ->method('get')
            ->with($creditmemoId)
            ->willReturn($this->creditmemoMock);

        $this->returnProcessorMock->expects($this->once())
            ->method('execute')
            ->with($this->creditmemoMock, $this->orderMock, $returnToStockItems);

        $this->stockConfigurationMock->expects($this->once())
            ->method('isAutoReturnEnabled')
            ->willReturn(false);

        $this->assertEquals(
            $this->returnTOStock->afterExecute(
                $this->refundOrderMock,
                $creditmemoId,
                $orderId,
                $items,
                false,
                false,
                null,
                $this->creditmemoCreationArgumentsMock
            ),
            $creditmemoId
        );
    }
}

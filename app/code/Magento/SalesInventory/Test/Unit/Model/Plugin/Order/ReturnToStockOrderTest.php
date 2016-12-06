<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Test\Unit\Model\Plugin\Order;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\SalesInventory\Model\Order\ReturnProcessor;
use Magento\SalesInventory\Model\Plugin\Order\ReturnToStockOrder;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsExtensionInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\RefundOrderInterface;

/**
 * Class ReturnToStockOrderTest
 */
class ReturnToStockOrderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ReturnToStockOrder */
    private $returnTOStock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ReturnProcessor
     */
    private $returnProcessorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CreditmemoRepositoryInterface
     */
    private $creditmemoRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OrderRepositoryInterface
     */
    private $orderRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RefundOrderInterface
     */
    private $refundOrderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CreditmemoCreationArgumentsInterface
     */
    private $creditmemoCreationArgumentsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OrderInterface
     */
    private $orderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CreditmemoInterface
     */
    private $creditmemoMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StockConfigurationInterface
     */
    private $stockConfigurationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CreditmemoCreationArgumentsInterface
     */
    private $extencionAttributesMock;

    protected function setUp()
    {
        $this->returnProcessorMock = $this->getMockBuilder(ReturnProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoRepositoryMock = $this->getMockBuilder(CreditmemoRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->refundOrderMock = $this->getMockBuilder(RefundOrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoCreationArgumentsMock = $this->getMockBuilder(CreditmemoCreationArgumentsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extencionAttributesMock = $this->getMockBuilder(CreditmemoCreationArgumentsExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getReturnToStockItems'])
            ->getMock();
        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockConfigurationMock = $this->getMockBuilder(StockConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

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
            ->willReturn($this->extencionAttributesMock);

        $this->extencionAttributesMock->expects($this->exactly(2))
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

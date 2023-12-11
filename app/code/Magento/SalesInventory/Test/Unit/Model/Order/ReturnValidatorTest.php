<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesInventory\Test\Unit\Model\Order;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\SalesInventory\Model\Order\ReturnValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReturnValidatorTest extends TestCase
{
    /**
     * @var MockObject|OrderItemRepositoryInterface
     */
    private $orderItemRepositoryMock;

    /**
     * @var MockObject|CreditmemoInterface
     */
    private $creditMemoMock;

    /**
     * @var MockObject|CreditmemoItemInterface
     */
    private $creditMemoItemMock;

    /**
     * @var MockObject|OrderItemInterface
     */
    private $orderItemMock;

    /**
     * @var ReturnValidator
     */
    private $returnValidator;

    protected function setUp(): void
    {
        $this->orderItemRepositoryMock = $this->getMockBuilder(OrderItemRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->creditMemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->creditMemoItemMock = $this->getMockBuilder(CreditmemoItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->orderItemMock = $this->getMockBuilder(OrderItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->returnValidator = new ReturnValidator(
            $this->orderItemRepositoryMock
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testValidate(
        $expectedResult,
        $returnToStockItems,
        $orderItemId,
        $creditMemoItemId,
        $productSku = null
    ) {
        $this->creditMemoMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->creditMemoItemMock]);

        $this->orderItemRepositoryMock->expects($this->once())
            ->method('get')
            ->with($returnToStockItems[0])
            ->willReturn($this->orderItemMock);

        $this->orderItemMock->expects($this->once())
            ->method('getItemId')
            ->willReturn($orderItemId);

        $this->creditMemoItemMock->expects($this->once())
            ->method('getOrderItemId')
            ->willReturn($creditMemoItemId);

        if ($productSku) {
            $this->orderItemMock->expects($this->once())
                ->method('getSku')
                ->willReturn($productSku);
        }

        $this->assertEquals(
            $this->returnValidator->validate($returnToStockItems, $this->creditMemoMock),
            $expectedResult
        );
    }

    public function testValidationWithWrongOrderItems()
    {
        $returnToStockItems = [1];
        $this->creditMemoMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->creditMemoItemMock]);
        $this->orderItemRepositoryMock->expects($this->once())
            ->method('get')
            ->with($returnToStockItems[0])
            ->willThrowException(new NoSuchEntityException());

        $this->assertEquals(
            $this->returnValidator->validate($returnToStockItems, $this->creditMemoMock),
            __('The return to stock argument contains product item that is not part of the original order.')
        );
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'PostirivValidationTest' => [null, [1], 1, 1],
            'WithWrongReturnToStockItems' => [
                __('The "%1" product is not part of the current creditmemo.', 'sku1'), [2], 2, 1, 'sku1',
            ],
        ];
    }
}

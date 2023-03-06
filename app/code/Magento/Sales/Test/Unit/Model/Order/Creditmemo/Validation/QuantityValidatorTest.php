<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Creditmemo\Validation;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo\Validation\QuantityValidator;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Api\Data\StoreConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuantityValidatorTest extends TestCase
{
    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var InvoiceRepositoryInterface|MockObject
     */
    private $invoiceRepositoryMock;

    /**
     * @var QuantityValidator
     */
    private $validator;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrencyMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->invoiceRepositoryMock = $this->getMockBuilder(InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->priceCurrencyMock = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->validator = new QuantityValidator(
            $this->orderRepositoryMock,
            $this->invoiceRepositoryMock,
            $this->priceCurrencyMock
        );
    }

    public function testValidateWithoutItems()
    {
        $creditmemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['isValidGrandTotal'])
            ->getMockForAbstractClass();
        $creditmemoMock->expects($this->exactly(2))->method('getOrderId')
            ->willReturn(1);
        $creditmemoMock->expects($this->once())->method('getItems')
            ->willReturn([]);
        $orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $orderMock->expects($this->once())->method('getItems')
            ->willReturn([]);

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn($orderMock);
        $creditmemoMock->expects($this->once())->method('isValidGrandTotal')
            ->willReturn(false);
        $this->assertEquals(
            [
                __('The credit memo\'s total must be positive.')
            ],
            $this->validator->validate($creditmemoMock)
        );
    }

    public function testValidateWithoutOrder()
    {
        $creditmemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $creditmemoMock->expects($this->once())->method('getOrderId')
            ->willReturn(null);
        $creditmemoMock->expects($this->never())->method('getItems');
        $this->assertEquals(
            [__('Order Id is required for creditmemo document')],
            $this->validator->validate($creditmemoMock)
        );
    }

    public function testValidateWithWrongItemId()
    {
        $orderId = 1;
        $orderItemId = 1;
        $creditmemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['isValidGrandTotal'])
            ->getMockForAbstractClass();
        $creditmemoMock->expects($this->once())->method('isValidGrandTotal')
            ->willReturn(true);
        $creditmemoMock->expects($this->exactly(2))->method('getOrderId')
            ->willReturn($orderId);
        $creditmemoItemMock = $this->getMockBuilder(
            CreditmemoItemInterface::class
        )->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $creditmemoItemMock->expects($this->once())->method('getOrderItemId')
            ->willReturn($orderItemId);
        $creditmemoItemSku = 'sku';
        $creditmemoItemMock->expects($this->once())->method('getSku')
            ->willReturn($creditmemoItemSku);
        $creditmemoMock->expects($this->exactly(1))->method('getItems')
            ->willReturn([$creditmemoItemMock]);

        $orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $orderMock->expects($this->once())->method('getItems')
            ->willReturn([]);

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($orderMock);

        $this->assertEquals(
            [
                __(
                    'The creditmemo contains product SKU "%1" that is not part of the original order.',
                    $creditmemoItemSku
                ),
            ],
            $this->validator->validate($creditmemoMock)
        );
    }

    private function getCreditMemoMockParams()
    {
        return [
            $this->createMock(\Magento\Framework\Model\Context::class),
            $this->createMock(\Magento\Framework\Registry::class),
            $this->createMock(\Magento\Framework\Api\ExtensionAttributesFactory::class),
            $this->createMock(\Magento\Framework\Api\AttributeValueFactory::class),
            $this->createMock(\Magento\Sales\Model\Order\Creditmemo\Config::class),
            $this->createMock(\Magento\Sales\Model\OrderFactory::class),
            $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\CollectionFactory::class),
            $this->createMock(\Magento\Framework\Math\CalculatorFactory::class),
            $this->createMock(\Magento\Store\Model\StoreManagerInterface::class),
            $this->createMock(\Magento\Sales\Model\Order\Creditmemo\CommentFactory::class),
            $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment\CollectionFactory::class),
            $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class),
            $this->createMock(\Magento\Framework\Model\ResourceModel\AbstractResource::class),
            $this->createMock(\Magento\Framework\Data\Collection\AbstractDb::class),
            [],
            $this->createMock(\Magento\Sales\Model\Order\InvoiceFactory::class),
            $this->createMock(ScopeConfigInterface::class),
            $this->createMock(\Magento\Sales\Api\OrderRepositoryInterface::class)
        ];
    }

    /**
     * @param int $orderId
     * @param int $orderItemId
     * @param int $qtyToRequest
     * @param int $qtyToRefund
     * @param string $sku
     * @param int $total
     * @param array $expected
     * @param bool $isQtyDecimalAllowed
     * @param bool $isAllowZeroGrandTotal
     * @dataProvider dataProviderForValidateQty
     */
    public function testValidate(
        $orderId,
        $orderItemId,
        $qtyToRequest,
        $qtyToRefund,
        $sku,
        $total,
        array $expected,
        bool $isQtyDecimalAllowed,
        bool $isAllowZeroGrandTotal
    ) {
        $scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $scopeConfig->expects($this->any())->method('getValue')->willReturn($isAllowZeroGrandTotal);
        $creditMemoConstructorParams = $this->getCreditMemoMockParams();
        $creditMemoConstructorParams[16] = $scopeConfig;

        $creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->setConstructorArgs($creditMemoConstructorParams)
            ->onlyMethods(['getOrderId', 'getItems', 'getGrandTotal', '_construct'])
            ->getMockForAbstractClass();

        $creditmemoMock->expects($this->exactly(2))->method('getOrderId')
            ->willReturn($orderId);
        $creditmemoMock->expects($this->once())->method('getGrandTotal')
            ->willReturn($total);

        $creditmemoItemMock = $this->getMockBuilder(
            CreditmemoItemInterface::class
        )->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $creditmemoItemMock->expects($this->exactly(2))->method('getOrderItemId')
            ->willReturn($orderItemId);
        $creditmemoItemMock->expects($this->never())->method('getSku')
            ->willReturn($sku);
        $creditmemoItemMock->expects($this->atLeastOnce())->method('getQty')
            ->willReturn($qtyToRequest);
        $creditmemoMock->expects($this->exactly(1))->method('getItems')
            ->willReturn([$creditmemoItemMock]);

        $orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $orderItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderItemMock->expects($this->any())->method('getIsQtyDecimal')
            ->willReturn($isQtyDecimalAllowed);
        $orderItemMock->expects($this->any())->method('getQtyToRefund')
            ->willReturn($qtyToRefund);
        $creditmemoItemMock->expects($this->any())->method('getQty')
            ->willReturn($qtyToRequest);
        $orderMock->expects($this->once())->method('getItems')
            ->willReturn([$orderItemMock]);
        $orderItemMock->expects($this->once())->method('getItemId')
            ->willReturn($orderItemId);
        $orderItemMock->expects($this->any())->method('getSku')
            ->willReturn($sku);

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($orderMock);

        $this->assertEquals(
            $expected,
            $this->validator->validate($creditmemoMock)
        );
    }

    /**
     * @return array
     */
    public function dataProviderForValidateQty()
    {
        $sku = 'sku';

        return [
            [
                'orderId' => 1,
                'orderItemId' => 1,
                'qtyToRequest' => 1,
                'qtyToRefund' => 1,
                'sku',
                'total' => 15,
                'expected' => [],
                'isQtyDecimalAllowed' => false,
                'isAllowZeroGrandTotal' => true
            ],
            [
                'orderId' => 1,
                'orderItemId' => 1,
                'qtyToRequest' => 0,
                'qtyToRefund' => 0,
                'sku',
                'total' => 15,
                'expected' => [],
                'isQtyDecimalAllowed' => false,
                'isAllowZeroGrandTotal' => true
            ],
            [
                'orderId' => 1,
                'orderItemId' => 1,
                'qtyToRequest' => 1.5,
                'qtyToRefund' => 3,
                'sku',
                'total' => 5,
                'expected' => [
                    __(
                        'We found an invalid quantity to refund item "%1".',
                        $sku
                    )
                ],
                'isQtyDecimalAllowed' => false,
                'isAllowZeroGrandTotal' => true
            ],
            [
                'orderId' => 1,
                'orderItemId' => 1,
                'qtyToRequest' => 2,
                'qtyToRefund' => 1,
                'sku',
                'total' => 0,
                'expected' => [
                    __(
                        'The quantity to creditmemo must not be greater than the unrefunded quantity'
                        . ' for product SKU "%1".',
                        $sku
                    ),
                    __('The credit memo\'s total must be positive.')
                ],
                'isQtyDecimalAllowed' => false,
                'isAllowZeroGrandTotal' => false
            ],
            [
                'orderId' => 1,
                'orderItemId' => 1,
                'qtyToRequest' => 1,
                'qtyToRefund' => 1,
                'sku',
                'total' => 0,
                'expected' => [],
                'isQtyDecimalAllowed' => false,
                'isAllowZeroGrandTotal' => true
            ]
        ];
    }
}

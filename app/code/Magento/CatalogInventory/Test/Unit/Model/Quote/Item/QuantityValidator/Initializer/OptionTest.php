<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\Quote\Item\QuantityValidator\Initializer;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option
     */
    protected $validator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $qtyItemListMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $optionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteItemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockItemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultMock;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockState;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var int
     */
    protected $productId = 111;

    /**
     * @var int
     */
    protected $websiteId = 111;

    protected function setUp(): void
    {
        $optionMethods = [
            'getValue',
            'getProduct',
            'setIsQtyDecimal',
            'setHasQtyOptionUpdate',
            'setValue',
            'setMessage',
            'setBackorders',
            '__wakeup',
        ];

        $this->optionMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Item\Option::class, $optionMethods);

        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId', '__wakeup']);
        $store->expects($this->any())->method('getWebsiteId')->willReturn($this->websiteId);

        $methods = ['getQtyToAdd', '__wakeup', 'getId', 'updateQtyOption', 'setData', 'getQuoteId', 'getStore'];
        $this->quoteItemMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, $methods);
        $this->quoteItemMock->expects($this->any())->method('getStore')->willReturn($store);

        $stockItemMethods = [
            'setIsChildItem',
            'setSuppressCheckQtyIncrements',
            '__wakeup',
            'unsIsChildItem',
            'getItemId',
            'setProductName'
        ];

        $this->stockItemMock = $this->getMockBuilder(\Magento\CatalogInventory\Api\Data\StockItemInterface::class)
            ->setMethods($stockItemMethods)
            ->getMockForAbstractClass();
        $productMethods = ['getId', '__wakeup', 'getStore'];
        $this->productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, $productMethods, []);
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId', '__wakeup']);
        $store->expects($this->any())->method('getWebsiteId')->willReturn($this->websiteId);
        $this->productMock->expects($this->any())->method('getStore')->willReturn($store);

        $this->qtyItemListMock = $this->createMock(
            \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList::class
        );
        $resultMethods = [
            'getItemIsQtyDecimal',
            'getHasQtyOptionUpdate',
            'getOrigQty',
            'getMessage',
            'getItemBackorders',
            '__wakeup',
        ];
        $this->resultMock = $this->createPartialMock(\Magento\Framework\DataObject::class, $resultMethods);

        $this->stockRegistry = $this->createMock(
            \Magento\CatalogInventory\Api\StockRegistryInterface::class
        );

        $this->stockState = $this->createMock(
            \Magento\CatalogInventory\Api\StockStateInterface::class
        );

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->validator = $this->objectManager->getObject(
            \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option::class,
            [
                'quoteItemQtyList' => $this->qtyItemListMock,
                'stockRegistry' => $this->stockRegistry,
                'stockState' => $this->stockState
            ]
        );
    }

    public function testInitializeWhenResultIsDecimalGetBackordersMessageHasOptionQtyUpdate()
    {
        $optionValue = 5;
        $qtyForCheck = 50;
        $qty = 10;
        $qtyToAdd = 20;
        $this->optionMock->expects($this->once())->method('getValue')->willReturn($optionValue);
        $this->quoteItemMock->expects($this->exactly(2))->method('getQtyToAdd')->willReturn($qtyToAdd);
        $this->optionMock->expects($this->any())->method('getProduct')->willReturn($this->productMock);

        $this->stockItemMock->expects($this->once())->method('setIsChildItem')->with(true);
        $this->stockItemMock->expects($this->once())->method('getItemId')->willReturn(true);

        $this->stockRegistry
            ->expects($this->once())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $this->productMock->expects($this->any())->method('getId')->willReturn($this->productId);
        $this->quoteItemMock->expects($this->any())->method('getId')->willReturn('quote_item_id');
        $this->quoteItemMock->expects($this->once())->method('getQuoteId')->willReturn('quote_id');
        $this->qtyItemListMock->expects(
            $this->once()
        )->method(
            'getQty'
        )->with(
            $this->productId,
            'quote_item_id',
            'quote_id',
            $qtyToAdd * $optionValue
        )->willReturn(
            $qtyForCheck
        );
        $this->stockState->expects($this->once())->method('checkQuoteItemQty')->with(
            $this->productId,
            $qty * $optionValue,
            $qtyForCheck,
            $optionValue,
            $this->websiteId
        )->willReturn(
            $this->resultMock
        );
        $this->resultMock->expects(
            $this->exactly(2)
        )->method(
            'getItemIsQtyDecimal'
        )->willReturn(
            'is_decimal'
        );
        $this->optionMock->expects($this->once())->method('setIsQtyDecimal')->with('is_decimal');
        $this->resultMock->expects($this->once())->method('getHasQtyOptionUpdate')->willReturn(true);
        $this->optionMock->expects($this->once())->method('setHasQtyOptionUpdate')->with(true);
        $this->resultMock->expects($this->exactly(2))->method('getOrigQty')->willReturn('orig_qty');
        $this->quoteItemMock->expects($this->once())->method('updateQtyOption')->with($this->optionMock, 'orig_qty');
        $this->optionMock->expects($this->once())->method('setValue')->with('orig_qty');
        $this->quoteItemMock->expects($this->once())->method('setData')->with('qty', $qty);
        $this->resultMock->expects($this->exactly(3))->method('getMessage')->willReturn('message');
        $this->optionMock->expects($this->once())->method('setMessage')->with('message');
        $this->resultMock->expects(
            $this->exactly(2)
        )->method(
            'getItemBackorders'
        )->willReturn(
            'backorders'
        );
        $this->optionMock->expects($this->once())->method('setBackorders')->with('backorders');

        $this->stockItemMock->expects($this->once())->method('unsIsChildItem');
        $this->resultMock->expects($this->once())->method('getItemQty')->willReturn($qty);
        $this->validator->initialize($this->optionMock, $this->quoteItemMock, $qty);
    }

    public function testInitializeWhenResultNotDecimalGetBackordersMessageHasOptionQtyUpdate()
    {
        $optionValue = 5;
        $qtyForCheck = 50;
        $qty = 10;
        $this->optionMock->expects($this->once())->method('getValue')->willReturn($optionValue);
        $this->quoteItemMock->expects($this->once())->method('getQtyToAdd')->willReturn(false);
        $this->optionMock->expects($this->any())->method('getProduct')->willReturn($this->productMock);

        $this->stockItemMock->expects($this->once())->method('setIsChildItem')->with(true);
        $this->stockItemMock->expects($this->once())->method('getItemId')->willReturn(true);

        $this->stockRegistry
            ->expects($this->once())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $this->productMock->expects($this->any())->method('getId')->willReturn($this->productId);
        $this->quoteItemMock->expects($this->any())->method('getId')->willReturn('quote_item_id');
        $this->quoteItemMock->expects($this->once())->method('getQuoteId')->willReturn('quote_id');
        $this->qtyItemListMock->expects(
            $this->once()
        )->method(
            'getQty'
        )->with(
            $this->productId,
            'quote_item_id',
            'quote_id',
            $qty * $optionValue
        )->willReturn(
            $qtyForCheck
        );
        $this->stockState->expects($this->once())->method('checkQuoteItemQty')->with(
            $this->productId,
            $qty * $optionValue,
            $qtyForCheck,
            $optionValue,
            $this->websiteId
        )->willReturn(
            $this->resultMock
        );
        $this->resultMock->expects($this->once())->method('getItemIsQtyDecimal')->willReturn(null);
        $this->optionMock->expects($this->never())->method('setIsQtyDecimal');
        $this->resultMock->expects($this->once())->method('getHasQtyOptionUpdate')->willReturn(null);
        $this->optionMock->expects($this->never())->method('setHasQtyOptionUpdate');
        $this->resultMock->expects($this->once())->method('getMessage')->willReturn(null);
        $this->resultMock->expects($this->once())->method('getItemBackorders')->willReturn(null);
        $this->optionMock->expects($this->never())->method('setBackorders');

        $this->stockItemMock->expects($this->once())->method('unsIsChildItem');
        $this->validator->initialize($this->optionMock, $this->quoteItemMock, $qty);
    }

    /**
     */
    public function testInitializeWithInvalidOptionQty()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('The stock item for Product in option is not valid.');

        $optionValue = 5;
        $qty = 10;
        $this->optionMock->expects($this->once())->method('getValue')->willReturn($optionValue);
        $this->quoteItemMock->expects($this->once())->method('getQtyToAdd')->willReturn(false);
        $this->productMock->expects($this->any())->method('getId')->willReturn($this->productId);
        $this->optionMock->expects($this->any())->method('getProduct')->willReturn($this->productMock);
        $this->stockItemMock->expects($this->once())->method('getItemId')->willReturn(false);

        $this->stockRegistry
            ->expects($this->once())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $this->validator->initialize($this->optionMock, $this->quoteItemMock, $qty);
    }
}

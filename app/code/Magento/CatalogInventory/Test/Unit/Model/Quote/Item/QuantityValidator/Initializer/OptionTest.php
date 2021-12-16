<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Quote\Item\QuantityValidator\Initializer;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionTest extends TestCase
{
    /**
     * @var Option
     */
    protected $validator;

    /**
     * @var MockObject
     */
    protected $qtyItemListMock;

    /**
     * @var MockObject
     */
    protected $optionMock;

    /**
     * @var MockObject
     */
    protected $quoteItemMock;

    /**
     * @var MockObject
     */
    protected $stockItemMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $resultMock;

    /**
     * @var StockRegistryInterface|MockObject
     */
    protected $stockRegistry;

    /**
     * @var StockStateInterface|MockObject
     */
    protected $stockState;

    /**
     * @var ObjectManager
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
        $this->optionMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
            ->addMethods(['setIsQtyDecimal', 'setHasQtyOptionUpdate', 'setValue', 'setMessage', 'setBackorders'])
            ->onlyMethods(['getValue', 'getProduct'])
            ->disableOriginalConstructor()
            ->getMock();

        $store = $this->createPartialMock(Store::class, ['getWebsiteId']);
        $store->expects($this->any())->method('getWebsiteId')->willReturn($this->websiteId);

        $this->quoteItemMock = $this->getMockBuilder(Item::class)
            ->addMethods(['getQtyToAdd'])
            ->onlyMethods(['getId', 'updateQtyOption', 'setData', 'getQuoteId', 'getStore'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteItemMock->expects($this->any())->method('getStore')->willReturn($store);

        $this->stockItemMock = $this->getMockBuilder(StockItemInterface::class)
            ->setMethods(
                [
                    'setIsChildItem',
                    'setSuppressCheckQtyIncrements',
                    'unsIsChildItem',
                    'getItemId',
                    'setProductName'
                ]
            )
            ->getMockForAbstractClass();
        $this->productMock = $this->createPartialMock(Product::class, ['getId', 'getStore']);
        $store = $this->createPartialMock(Store::class, ['getWebsiteId']);
        $store->expects($this->any())->method('getWebsiteId')->willReturn($this->websiteId);
        $this->productMock->expects($this->any())->method('getStore')->willReturn($store);

        $this->qtyItemListMock = $this->createMock(
            QuoteItemQtyList::class
        );
        $this->resultMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(
                ['getItemIsQtyDecimal', 'getHasQtyOptionUpdate', 'getOrigQty', 'getMessage', 'getItemBackorders', 'getItemQty']
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockRegistry = $this->getMockForAbstractClass(
            StockRegistryInterface::class
        );

        $this->stockState = $this->getMockForAbstractClass(
            StockStateInterface::class
        );

        $this->objectManager = new ObjectManager($this);
        $this->validator = $this->objectManager->getObject(
            Option::class,
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

    public function testInitializeWithInvalidOptionQty()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
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

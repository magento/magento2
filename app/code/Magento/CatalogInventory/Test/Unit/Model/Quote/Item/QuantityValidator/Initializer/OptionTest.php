<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\Quote\Item\QuantityValidator\Initializer;

class OptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option
     */
    protected $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $qtyItemListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultMock;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface|\PHPUnit_Framework_MockObject_MockObject
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

    protected function setUp()
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
        $this->optionMock = $this->getMock(
            'Magento\Quote\Model\Quote\Item\Option',
            $optionMethods,
            [],
            '',
            false
        );

        $store = $this->getMock(
            '\Magento\Store\Model\Store',
            ['getWebsiteId', '__wakeup'],
            [],
            '',
            false
        );
        $store->expects($this->any())->method('getWebsiteId')->willReturn($this->websiteId);

        $methods = ['getQtyToAdd', '__wakeup', 'getId', 'updateQtyOption', 'setData', 'getQuoteId', 'getStore'];
        $this->quoteItemMock = $this->getMock('Magento\Quote\Model\Quote\Item', $methods, [], '', false);
        $this->quoteItemMock->expects($this->any())->method('getStore')->willReturn($store);

        $stockItemMethods = [
            'setIsChildItem',
            'setSuppressCheckQtyIncrements',
            '__wakeup',
            'unsIsChildItem',
            'getItemId',
        ];

        $this->stockItemMock = $this->getMock(
            'Magento\CatalogInventory\Api\Data\StockItem',
            $stockItemMethods,
            [],
            '',
            false
        );
        $productMethods = ['getId', '__wakeup', 'getStore'];
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', $productMethods, [], '', false);
        $store = $this->getMock(
            '\Magento\Store\Model\Store',
            ['getWebsiteId', '__wakeup'],
            [],
            '',
            false
        );
        $store->expects($this->any())->method('getWebsiteId')->willReturn($this->websiteId);
        $this->productMock->expects($this->any())->method('getStore')->willReturn($store);

        $this->qtyItemListMock = $this->getMock(
            'Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList',
            [],
            [],
            '',
            false
        );
        $resultMethods = [
            'getItemIsQtyDecimal',
            'getHasQtyOptionUpdate',
            'getOrigQty',
            'getMessage',
            'getItemBackorders',
            '__wakeup',
        ];
        $this->resultMock = $this->getMock('Magento\Framework\DataObject', $resultMethods, [], '', false);

        $this->stockRegistry = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\StockRegistryInterface',
            ['getStockItem']
        );

        $this->stockState = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\StockStateInterface',
            ['checkQuoteItemQty']
        );

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->validator = $this->objectManager->getObject(
            'Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option',
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
        $this->optionMock->expects($this->once())->method('getValue')->will($this->returnValue($optionValue));
        $this->quoteItemMock->expects($this->exactly(2))->method('getQtyToAdd')->will($this->returnValue($qtyToAdd));
        $this->optionMock->expects($this->any())->method('getProduct')->will($this->returnValue($this->productMock));

        $this->stockItemMock->expects($this->once())->method('setIsChildItem')->with(true);
        $this->stockItemMock->expects($this->once())->method('setSuppressCheckQtyIncrements')->with(true);
        $this->stockItemMock->expects($this->once())->method('getItemId')->will($this->returnValue(true));

        $this->stockRegistry
            ->expects($this->once())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));

        $this->productMock->expects($this->any())->method('getId')->will($this->returnValue($this->productId));
        $this->quoteItemMock->expects($this->any())->method('getId')->will($this->returnValue('quote_item_id'));
        $this->quoteItemMock->expects($this->once())->method('getQuoteId')->will($this->returnValue('quote_id'));
        $this->qtyItemListMock->expects(
            $this->once()
        )->method(
            'getQty'
        )->with(
            $this->productId,
            'quote_item_id',
            'quote_id',
            $qtyToAdd * $optionValue
        )->will(
            $this->returnValue($qtyForCheck)
        );
        $this->stockState->expects($this->once())->method('checkQuoteItemQty')->with(
            $this->productId,
            $qty * $optionValue,
            $qtyForCheck,
            $optionValue,
            $this->websiteId
        )->will(
            $this->returnValue($this->resultMock)
        );
        $this->resultMock->expects(
            $this->exactly(2)
        )->method(
            'getItemIsQtyDecimal'
        )->will(
            $this->returnValue('is_decimal')
        );
        $this->optionMock->expects($this->once())->method('setIsQtyDecimal')->with('is_decimal');
        $this->resultMock->expects($this->once())->method('getHasQtyOptionUpdate')->will($this->returnValue(true));
        $this->optionMock->expects($this->once())->method('setHasQtyOptionUpdate')->with(true);
        $this->resultMock->expects($this->exactly(2))->method('getOrigQty')->will($this->returnValue('orig_qty'));
        $this->quoteItemMock->expects($this->once())->method('updateQtyOption')->with($this->optionMock, 'orig_qty');
        $this->optionMock->expects($this->once())->method('setValue')->with('orig_qty');
        $this->quoteItemMock->expects($this->once())->method('setData')->with('qty', $qty);
        $this->resultMock->expects($this->exactly(3))->method('getMessage')->will($this->returnValue('message'));
        $this->optionMock->expects($this->once())->method('setMessage')->with('message');
        $this->resultMock->expects(
            $this->exactly(2)
        )->method(
            'getItemBackorders'
        )->will(
            $this->returnValue('backorders')
        );
        $this->optionMock->expects($this->once())->method('setBackorders')->with('backorders');

        $this->stockItemMock->expects($this->once())->method('unsIsChildItem');
        $this->validator->initialize($this->optionMock, $this->quoteItemMock, $qty);
    }

    public function testInitializeWhenResultNotDecimalGetBackordersMessageHasOptionQtyUpdate()
    {
        $optionValue = 5;
        $qtyForCheck = 50;
        $qty = 10;
        $this->optionMock->expects($this->once())->method('getValue')->will($this->returnValue($optionValue));
        $this->quoteItemMock->expects($this->once())->method('getQtyToAdd')->will($this->returnValue(false));
        $this->optionMock->expects($this->any())->method('getProduct')->will($this->returnValue($this->productMock));

        $this->stockItemMock->expects($this->once())->method('setIsChildItem')->with(true);
        $this->stockItemMock->expects($this->once())->method('setSuppressCheckQtyIncrements')->with(true);
        $this->stockItemMock->expects($this->once())->method('getItemId')->will($this->returnValue(true));

        $this->stockRegistry
            ->expects($this->once())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));

        $this->productMock->expects($this->any())->method('getId')->will($this->returnValue($this->productId));
        $this->quoteItemMock->expects($this->any())->method('getId')->will($this->returnValue('quote_item_id'));
        $this->quoteItemMock->expects($this->once())->method('getQuoteId')->will($this->returnValue('quote_id'));
        $this->qtyItemListMock->expects(
            $this->once()
        )->method(
            'getQty'
        )->with(
            $this->productId,
            'quote_item_id',
            'quote_id',
            $qty * $optionValue
        )->will(
            $this->returnValue($qtyForCheck)
        );
        $this->stockState->expects($this->once())->method('checkQuoteItemQty')->with(
            $this->productId,
            $qty * $optionValue,
            $qtyForCheck,
            $optionValue,
            $this->websiteId
        )->will(
            $this->returnValue($this->resultMock)
        );
        $this->resultMock->expects($this->once())->method('getItemIsQtyDecimal')->will($this->returnValue(null));
        $this->optionMock->expects($this->never())->method('setIsQtyDecimal');
        $this->resultMock->expects($this->once())->method('getHasQtyOptionUpdate')->will($this->returnValue(null));
        $this->optionMock->expects($this->never())->method('setHasQtyOptionUpdate');
        $this->resultMock->expects($this->once())->method('getMessage')->will($this->returnValue(null));
        $this->resultMock->expects($this->once())->method('getItemBackorders')->will($this->returnValue(null));
        $this->optionMock->expects($this->never())->method('setBackorders');

        $this->stockItemMock->expects($this->once())->method('unsIsChildItem');
        $this->validator->initialize($this->optionMock, $this->quoteItemMock, $qty);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The stock item for Product in option is not valid.
     */
    public function testInitializeWithInvalidOptionQty()
    {
        $optionValue = 5;
        $qty = 10;
        $this->optionMock->expects($this->once())->method('getValue')->will($this->returnValue($optionValue));
        $this->quoteItemMock->expects($this->once())->method('getQtyToAdd')->will($this->returnValue(false));
        $this->productMock->expects($this->any())->method('getId')->will($this->returnValue($this->productId));
        $this->optionMock->expects($this->any())->method('getProduct')->will($this->returnValue($this->productMock));
        $this->stockItemMock->expects($this->once())->method('getItemId')->will($this->returnValue(false));

        $this->stockRegistry
            ->expects($this->once())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));

        $this->validator->initialize($this->optionMock, $this->quoteItemMock, $qty);
    }
}

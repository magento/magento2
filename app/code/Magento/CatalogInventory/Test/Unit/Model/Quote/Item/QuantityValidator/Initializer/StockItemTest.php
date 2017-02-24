<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\Quote\Item\QuantityValidator\Initializer;

use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\StockItem
     */
    protected $model;

    /**
     * @var QuoteItemQtyList| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteItemQtyList;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStateMock;

    protected function setUp()
    {
        $this->quoteItemQtyList = $this
            ->getMockBuilder(\Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->typeConfig = $this
            ->getMockBuilder(\Magento\Catalog\Model\ProductTypes\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->stockStateMock = $this->getMockBuilder(\Magento\CatalogInventory\Api\StockStateInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManagerHelper->getObject(
            \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\StockItem::class,
            [
                'quoteItemQtyList' => $this->quoteItemQtyList,
                'typeConfig' => $this->typeConfig,
                'stockState' => $this->stockStateMock
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testInitializeWithSubitem()
    {
        $qty = 2;
        $parentItemQty = 3;
        $websiteId = 1;

        $stockItem = $this->getMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            ['checkQuoteItemQty', 'setProductName', 'setIsChildItem', 'hasIsChildItem', 'unsIsChildItem', '__wakeup'],
            [],
            '',
            false
        );
        $quoteItem = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->setMethods(
                [
                    'getParentItem',
                    'getProduct',
                    'getId',
                    'getQuoteId',
                    'setIsQtyDecimal',
                    'setData',
                    'setUseOldQty',
                    'setMessage',
                    'setBackorders',
                    '__wakeup',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $parentItem = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->setMethods(['getQty', 'setIsQtyDecimal', 'getProduct', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentProduct = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productTypeInstance = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $productTypeCustomOption = $this->getMockBuilder(
            \Magento\Catalog\Model\Product\Configuration\Item\Option::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $result = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(
                [
                    'getItemIsQtyDecimal',
                    'getHasQtyOptionUpdate',
                    'getOrigQty',
                    'getItemUseOldQty',
                    'getMessage',
                    'getItemBackorders',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $quoteItem->expects($this->any())->method('getParentItem')->will($this->returnValue($parentItem));
        $parentItem->expects($this->once())->method('getQty')->will($this->returnValue($parentItemQty));
        $quoteItem->expects($this->any())->method('getProduct')->will($this->returnValue($product));
        $product->expects($this->any())->method('getId')->will($this->returnValue('product_id'));
        $quoteItem->expects($this->once())->method('getId')->will($this->returnValue('quote_item_id'));
        $quoteItem->expects($this->once())->method('getQuoteId')->will($this->returnValue('quote_id'));
        $this->quoteItemQtyList->expects($this->any())
            ->method('getQty')
            ->with('product_id', 'quote_item_id', 'quote_id', 0)
            ->will($this->returnValue('summary_qty'));
        $this->stockStateMock->expects($this->once())
            ->method('checkQuoteItemQty')
            ->withAnyParameters()
            ->will($this->returnValue($result));
        $product->expects($this->once())
            ->method('getCustomOption')
            ->with('product_type')
            ->will($this->returnValue($productTypeCustomOption));
        $productTypeCustomOption->expects($this->once())
            ->method('getValue')
            ->will(($this->returnValue('option_value')));
        $this->typeConfig->expects($this->once())
            ->method('isProductSet')
            ->with('option_value')
            ->will($this->returnValue(true));
        $product->expects($this->once())->method('getName')->will($this->returnValue('product_name'));
        $product->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);
        $stockItem->expects($this->once())->method('setProductName')->with('product_name')->will($this->returnSelf());
        $stockItem->expects($this->once())->method('setIsChildItem')->with(true)->will($this->returnSelf());
        $stockItem->expects($this->once())->method('hasIsChildItem')->will($this->returnValue(true));
        $stockItem->expects($this->once())->method('unsIsChildItem');
        $result->expects($this->exactly(3))->method('getItemIsQtyDecimal')->will($this->returnValue(true));
        $quoteItem->expects($this->once())->method('setIsQtyDecimal')->with(true)->will($this->returnSelf());
        $parentItem->expects($this->once())->method('setIsQtyDecimal')->with(true)->will($this->returnSelf());
        $parentItem->expects($this->any())->method('getProduct')->will($this->returnValue($parentProduct));
        $result->expects($this->once())->method('getHasQtyOptionUpdate')->will($this->returnValue(true));
        $parentProduct->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($productTypeInstance));
        $productTypeInstance->expects($this->once())
            ->method('getForceChildItemQtyChanges')
            ->with($product)->will($this->returnValue(true));
        $result->expects($this->once())->method('getOrigQty')->will($this->returnValue('orig_qty'));
        $quoteItem->expects($this->once())->method('setData')->with('qty', 'orig_qty')->will($this->returnSelf());
        $result->expects($this->exactly(2))->method('getItemUseOldQty')->will($this->returnValue('item'));
        $quoteItem->expects($this->once())->method('setUseOldQty')->with('item')->will($this->returnSelf());
        $result->expects($this->exactly(2))->method('getMessage')->will($this->returnValue('message'));
        $quoteItem->expects($this->once())->method('setMessage')->with('message')->will($this->returnSelf());
        $result->expects($this->exactly(2))->method('getItemBackorders')->will($this->returnValue('backorders'));
        $quoteItem->expects($this->once())->method('setBackorders')->with('backorders')->will($this->returnSelf());

        $this->model->initialize($stockItem, $quoteItem, $qty);
    }

    public function testInitializeWithoutSubitem()
    {
        $qty = 3;
        $websiteId = 1;
        $productId = 1;

        $stockItem = $this->getMockBuilder(\Magento\CatalogInventory\Model\Stock\Item::class)
            ->setMethods(['checkQuoteItemQty', 'setProductName', 'setIsChildItem', 'hasIsChildItem', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $quoteItem = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->setMethods(['getProduct', 'getParentItem', 'getQtyToAdd', 'getId', 'getQuoteId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productTypeCustomOption = $this->getMockBuilder(
            \Magento\Catalog\Model\Product\Configuration\Item\Option::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $result = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(
                ['getItemIsQtyDecimal', 'getHasQtyOptionUpdate', 'getItemUseOldQty', 'getMessage', 'getItemBackorders']
            )
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);
        $product->expects($this->any())
            ->method('getId')
            ->willReturn($productId);
        $quoteItem->expects($this->once())->method('getParentItem')->will($this->returnValue(false));
        $quoteItem->expects($this->once())->method('getQtyToAdd')->will($this->returnValue(false));
        $quoteItem->expects($this->any())->method('getProduct')->will($this->returnValue($product));
        $quoteItem->expects($this->once())->method('getId')->will($this->returnValue('quote_item_id'));
        $quoteItem->expects($this->once())->method('getQuoteId')->will($this->returnValue('quote_id'));
        $this->quoteItemQtyList->expects($this->any())
            ->method('getQty')
            ->with($productId, 'quote_item_id', 'quote_id', $qty)
            ->will($this->returnValue('summary_qty'));
        $this->stockStateMock->expects($this->once())
            ->method('checkQuoteItemQty')
            ->withAnyParameters()
            ->will($this->returnValue($result));
        $product->expects($this->once())
            ->method('getCustomOption')
            ->with('product_type')
            ->will($this->returnValue($productTypeCustomOption));
        $productTypeCustomOption->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue('option_value'));
        $this->typeConfig->expects($this->once())
            ->method('isProductSet')
            ->with('option_value')
            ->will($this->returnValue(true));
        $product->expects($this->once())->method('getName')->will($this->returnValue('product_name'));
        $stockItem->expects($this->once())->method('setProductName')->with('product_name')->will($this->returnSelf());
        $stockItem->expects($this->once())->method('setIsChildItem')->with(true)->will($this->returnSelf());
        $stockItem->expects($this->once())->method('hasIsChildItem')->will($this->returnValue(false));
        $result->expects($this->once())->method('getItemIsQtyDecimal')->will($this->returnValue(null));
        $result->expects($this->once())->method('getHasQtyOptionUpdate')->will($this->returnValue(false));
        $result->expects($this->once())->method('getItemUseOldQty')->will($this->returnValue(null));
        $result->expects($this->once())->method('getMessage')->will($this->returnValue(null));
        $result->expects($this->once())->method('getItemBackorders')->will($this->returnValue(null));

        $this->model->initialize($stockItem, $quoteItem, $qty);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Quote\Item\QuantityValidator\Initializer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\Option;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\StockItem;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\StockStateProvider;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockItemTest extends TestCase
{
    /**
     * @var StockItem
     */
    protected $model;

    /**
     * @var QuoteItemQtyList|MockObject
     */
    protected $quoteItemQtyList;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $typeConfig;

    /**
     * @var StockStateInterface|MockObject
     */
    protected $stockStateMock;

    /**
     * @var \Magento\CatalogInventory\Model\StockStateProviderInterface|MockObject
     */
    private $stockStateProviderMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->quoteItemQtyList = $this
            ->getMockBuilder(QuoteItemQtyList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->typeConfig = $this
            ->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManager($this);
        $this->stockStateMock = $this->getMockBuilder(StockStateInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->stockStateProviderMock = $this
            ->getMockBuilder(StockStateProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManagerHelper->getObject(
            StockItem::class,
            [
                'quoteItemQtyList' => $this->quoteItemQtyList,
                'typeConfig' => $this->typeConfig,
                'stockState' => $this->stockStateMock,
                'stockStateProvider' => $this->stockStateProviderMock
            ]
        );
    }

    /**
     * Test initialize with Subitem
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testInitializeWithSubitem()
    {
        $qty = 2;
        $parentItemQty = 3;
        $websiteId = 1;

        $stockItem = $this->getMockBuilder(Item::class)
            ->addMethods(['checkQuoteItemQty', 'setProductName', 'setIsChildItem', 'hasIsChildItem', 'unsIsChildItem'])
            ->onlyMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
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
                    'setStockStateResult'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $parentItem = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->setMethods(['getQty', 'setIsQtyDecimal', 'getProduct', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productTypeInstance = $this->getMockBuilder(AbstractType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $productTypeCustomOption = $this->getMockBuilder(
            Option::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $result = $this->getMockBuilder(DataObject::class)
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

        $quoteItem->expects($this->any())->method('getParentItem')->willReturn($parentItem);
        $parentItem->expects($this->once())->method('getQty')->willReturn($parentItemQty);
        $quoteItem->expects($this->any())->method('getProduct')->willReturn($product);
        $product->expects($this->any())->method('getId')->willReturn('product_id');
        $quoteItem->expects($this->once())->method('getId')->willReturn('quote_item_id');
        $quoteItem->expects($this->once())->method('getQuoteId')->willReturn('quote_id');
        $this->quoteItemQtyList->expects($this->any())
            ->method('getQty')
            ->with('product_id', 'quote_item_id', 'quote_id', 0)
            ->willReturn('summary_qty');
        $this->stockStateMock->expects($this->once())
            ->method('checkQuoteItemQty')
            ->withAnyParameters()
            ->willReturn($result);
        $this->stockStateProviderMock->expects($this->once())
            ->method('checkQuoteItemQty')
            ->withAnyParameters()
            ->willReturn($result);
        $product->expects($this->once())
            ->method('getCustomOption')
            ->with('product_type')
            ->willReturn($productTypeCustomOption);
        $productTypeCustomOption->expects($this->once())
            ->method('getValue')
            ->willReturn('option_value');
        $this->typeConfig->expects($this->once())
            ->method('isProductSet')
            ->with('option_value')
            ->willReturn(true);
        $product->expects($this->once())->method('getName')->willReturn('product_name');
        $product->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);
        $stockItem->expects($this->once())->method('setProductName')->with('product_name')->willReturnSelf();
        $stockItem->expects($this->once())->method('setIsChildItem')->with(true)->willReturnSelf();
        $stockItem->expects($this->once())->method('hasIsChildItem')->willReturn(true);
        $stockItem->expects($this->once())->method('unsIsChildItem');
        $result->expects($this->exactly(3))->method('getItemIsQtyDecimal')->willReturn(true);
        $quoteItem->expects($this->once())->method('setIsQtyDecimal')->with(true)->willReturnSelf();
        $parentItem->expects($this->once())->method('setIsQtyDecimal')->with(true)->willReturnSelf();
        $parentItem->expects($this->any())->method('getProduct')->willReturn($parentProduct);
        $result->expects($this->once())->method('getHasQtyOptionUpdate')->willReturn(true);
        $parentProduct->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productTypeInstance);
        $productTypeInstance->expects($this->once())
            ->method('getForceChildItemQtyChanges')
            ->with($product)->willReturn(true);
        $result->expects($this->once())->method('getOrigQty')->willReturn('orig_qty');
        $quoteItem->expects($this->once())->method('setData')->with('qty', 'orig_qty')->willReturnSelf();
        $result->expects($this->exactly(2))->method('getItemUseOldQty')->willReturn('item');
        $quoteItem->expects($this->once())->method('setUseOldQty')->with('item')->willReturnSelf();
        $result->expects($this->exactly(2))->method('getMessage')->willReturn('message');
        $quoteItem->expects($this->once())->method('setMessage')->with('message')->willReturnSelf();
        $result->expects($this->exactly(3))->method('getItemBackorders')->willReturn('backorders');
        $quoteItem->expects($this->once())->method('setBackorders')->with('backorders')->willReturnSelf();
        $quoteItem->expects($this->once())->method('setStockStateResult')->with($result)->willReturnSelf();

        $this->model->initialize($stockItem, $quoteItem, $qty);
    }

    /**
     * Test initialize without Subitem
     */
    public function testInitializeWithoutSubitem()
    {
        $qty = 3;
        $websiteId = 1;
        $productId = 1;

        $stockItem = $this->getMockBuilder(Item::class)
            ->setMethods(['checkQuoteItemQty', 'setProductName', 'setIsChildItem', 'hasIsChildItem', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $quoteItem = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->setMethods(['getProduct', 'getParentItem', 'getQtyToAdd', 'getId', 'getQuoteId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productTypeCustomOption = $this->getMockBuilder(
            Option::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $result = $this->getMockBuilder(DataObject::class)
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
        $quoteItem->expects($this->once())->method('getParentItem')->willReturn(false);
        $quoteItem->expects($this->once())->method('getQtyToAdd')->willReturn(false);
        $quoteItem->expects($this->any())->method('getProduct')->willReturn($product);
        $quoteItem->expects($this->once())->method('getId')->willReturn('quote_item_id');
        $quoteItem->expects($this->once())->method('getQuoteId')->willReturn('quote_id');
        $this->quoteItemQtyList->expects($this->any())
            ->method('getQty')
            ->with($productId, 'quote_item_id', 'quote_id', $qty)
            ->willReturn('summary_qty');
        $this->stockStateMock->expects($this->once())
            ->method('checkQuoteItemQty')
            ->withAnyParameters()
            ->willReturn($result);
        $this->stockStateProviderMock->expects($this->once())
            ->method('checkQuoteItemQty')
            ->withAnyParameters()
            ->willReturn($result);
        $product->expects($this->once())
            ->method('getCustomOption')
            ->with('product_type')
            ->willReturn($productTypeCustomOption);
        $productTypeCustomOption->expects($this->once())
            ->method('getValue')
            ->willReturn('option_value');
        $this->typeConfig->expects($this->once())
            ->method('isProductSet')
            ->with('option_value')
            ->willReturn(true);
        $product->expects($this->once())->method('getName')->willReturn('product_name');
        $stockItem->expects($this->once())->method('setProductName')->with('product_name')->willReturnSelf();
        $stockItem->expects($this->once())->method('setIsChildItem')->with(true)->willReturnSelf();
        $stockItem->expects($this->once())->method('hasIsChildItem')->willReturn(false);
        $result->expects($this->once())->method('getItemIsQtyDecimal')->willReturn(null);
        $result->expects($this->once())->method('getHasQtyOptionUpdate')->willReturn(false);
        $result->expects($this->once())->method('getItemUseOldQty')->willReturn(null);
        $result->expects($this->once())->method('getMessage')->willReturn(null);
        $result->expects($this->exactly(2))->method('getItemBackorders')->willReturn(null);

        $this->model->initialize($stockItem, $quoteItem, $qty);
    }
}

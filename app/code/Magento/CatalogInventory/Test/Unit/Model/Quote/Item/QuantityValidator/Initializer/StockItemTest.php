<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\StockStateProvider;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockItemTest extends TestCase
{
    use MockCreationTrait;
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
     * @var StockStateProviderInterface|MockObject
     */
    private $stockStateProviderMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->quoteItemQtyList = $this->createMock(QuoteItemQtyList::class);

        $this->typeConfig = $this->createMock(ConfigInterface::class);

        $objectManagerHelper = new ObjectManager($this);
        $this->stockStateMock = $this->createMock(StockStateInterface::class);

        $this->stockStateProviderMock = $this->createMock(StockStateProvider::class);

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

        $stockItem = $this->createPartialMockWithReflection(
            Item::class,
            ['checkQuoteItemQty', 'setProductName', 'setIsChildItem', 'hasIsChildItem', 'unsIsChildItem', '__wakeup']
        );
        $quoteItem = $this->createPartialMockWithReflection(
            QuoteItem::class,
            ['setIsQtyDecimal', 'setUseOldQty', 'setBackorders', 'setStockStateResult', 'getParentItem',
             'getProduct', 'getId', 'getQuoteId', 'setData', 'setMessage', '__wakeup']
        );
        $parentItem = $this->createPartialMockWithReflection(
            QuoteItem::class,
            ['setIsQtyDecimal', 'getQty', 'getProduct', '__wakeup']
        );
        $product = $this->createMock(Product::class);
        $parentProduct = $this->createMock(Product::class);
        $productTypeInstance = $this->createMock(AbstractType::class);
        $storeMock = $this->createMock(Store::class);
        $storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $productTypeCustomOption = $this->createMock(Option::class);
        $result = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getItemIsQtyDecimal', 'getHasQtyOptionUpdate', 'getOrigQty',
             'getItemUseOldQty', 'getMessage', 'getItemBackorders']
        );

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
        $this->stockStateProviderMock->expects($this->never())->method('checkQuoteItemQty');
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
        $result->expects($this->exactly(2))->method('getItemBackorders')->willReturn('backorders');
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

        $stockItem = $this->createPartialMockWithReflection(
            Item::class,
            ['checkQuoteItemQty', 'setProductName', 'setIsChildItem', 'hasIsChildItem', '__wakeup']
        );
        $storeMock = $this->createMock(Store::class);
        $storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $quoteItem = $this->createPartialMockWithReflection(
            QuoteItem::class,
            ['getQtyToAdd', 'getProduct', 'getParentItem', 'getId', 'getQuoteId', '__wakeup']
        );
        $product = $this->createMock(Product::class);
        $productTypeCustomOption = $this->createMock(Option::class);
        $result = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getItemIsQtyDecimal', 'getHasQtyOptionUpdate', 'getItemUseOldQty', 'getMessage', 'getItemBackorders']
        );
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
        $this->stockStateProviderMock->expects($this->never())->method('checkQuoteItemQty');
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
        $result->expects($this->exactly(1))->method('getItemBackorders')->willReturn(null);

        $this->model->initialize($stockItem, $quoteItem, $qty);
    }
}

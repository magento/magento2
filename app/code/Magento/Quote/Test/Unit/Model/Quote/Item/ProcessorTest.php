<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Item;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\Quote\Item\Processor;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    /** @var ItemFactory|MockObject */
    private $itemFactory;

    /** @var StoreManagerInterface|MockObject */
    private $storeManager;

    /** @var State|MockObject */
    private $appState;

    /** @var Processor */
    private $processor;

    /**
     * Prepare common mocks and instantiate Processor under test.
     */
    protected function setUp(): void
    {
        $this->itemFactory = $this->createMock(ItemFactory::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->appState = $this->createMock(State::class);
        $this->processor = new Processor(
            $this->itemFactory,
            $this->storeManager,
            $this->appState
        );
    }

    /**
     * Sets custom price when the provided custom price equals zero.
     */
    public function testPrepareSetsCustomPriceWhenCustomPriceIsZero(): void
    {
        $item = $this->createMock(Item::class);
        $candidate = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFinalPrice'])
            ->addMethods(['getStickWithinParent', 'getCartQty', 'getParentProductId'])
            ->getMock();
        $request = new DataObject([
            'custom_price' => 0,
            'reset_count' => 0,
            'id' => 10,
        ]);

        $candidate->method('getStickWithinParent')->willReturn(false);
        $candidate->method('getCartQty')->willReturn(2);
        $candidate->method('getFinalPrice')->willReturn(123.45);
        $candidate->method('getParentProductId')->willReturn(null);

        $item->method('getId')->willReturn(10);
        $item->method('getParentItem')->willReturn(null);

        $item->expects($this->never())->method('setData')->with(CartItemInterface::KEY_QTY, 0);
        $item->expects($this->once())->method('addQty')->with(2);
        $item->expects($this->once())->method('setPrice')->with(123.45);
        $item->expects($this->once())->method('setCustomPrice')->with(0);

        $this->processor->prepare($item, $request, $candidate);
    }

    /**
     * Does not set custom price when the provided custom price is null.
     */
    public function testPrepareDoesNotSetCustomPriceWhenNull(): void
    {
        $item = $this->createMock(Item::class);
        $candidate = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFinalPrice'])
            ->addMethods(['getStickWithinParent', 'getCartQty', 'getParentProductId'])
            ->getMock();
        $request = new DataObject([
            'custom_price' => null,
            'reset_count' => 0,
            'id' => 11,
        ]);

        $candidate->method('getStickWithinParent')->willReturn(false);
        $candidate->method('getCartQty')->willReturn(1);
        $candidate->method('getFinalPrice')->willReturn(50.00);
        $candidate->method('getParentProductId')->willReturn(null);

        $item->method('getId')->willReturn(11);
        $item->method('getParentItem')->willReturn(null);

        $item->expects($this->never())->method('setData')->with(CartItemInterface::KEY_QTY, 0);
        $item->expects($this->once())->method('addQty')->with(1);
        $item->expects($this->once())->method('setPrice')->with(50.00);
        $item->expects($this->never())->method('setCustomPrice');

        $this->processor->prepare($item, $request, $candidate);
    }

    /**
     * Skips setting custom price for child products.
     */
    public function testPrepareDoesNotSetCustomPriceForChildProduct(): void
    {
        $item = $this->createMock(Item::class);
        $candidate = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFinalPrice'])
            ->addMethods(['getStickWithinParent', 'getCartQty', 'getParentProductId'])
            ->getMock();
        $request = new DataObject([
            'custom_price' => 0,
            'reset_count' => 0,
            'id' => 12,
        ]);

        $candidate->method('getStickWithinParent')->willReturn(false);
        $candidate->method('getCartQty')->willReturn(3);
        $candidate->method('getFinalPrice')->willReturn(75.00);
        $candidate->method('getParentProductId')->willReturn(999); // child

        $item->method('getId')->willReturn(12);
        $item->method('getParentItem')->willReturn(null);

        $item->expects($this->once())->method('addQty')->with(3);
        $item->expects($this->once())->method('setPrice')->with(75.00);
        $item->expects($this->never())->method('setCustomPrice');

        $this->processor->prepare($item, $request, $candidate);
    }

    /**
     * Resets item qty when reset_count is set and the item id matches.
     */
    public function testPrepareResetsQtyWhenResetCountAndMatchingId(): void
    {
        $item = $this->createMock(Item::class);
        $candidate = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFinalPrice'])
            ->addMethods(['getStickWithinParent', 'getCartQty', 'getParentProductId'])
            ->getMock();
        $request = new DataObject([
            'custom_price' => 0,
            'reset_count' => 1,
            'id' => null,
        ]);

        $candidate->method('getStickWithinParent')->willReturn(false);
        $candidate->method('getCartQty')->willReturn(4);
        $candidate->method('getFinalPrice')->willReturn(10.00);
        $candidate->method('getParentProductId')->willReturn(null);

        $item->method('getParentItem')->willReturn(null);

        $item->expects($this->once())->method('setData')->with(CartItemInterface::KEY_QTY, 0);
        $item->expects($this->once())->method('addQty')->with(4);
        $item->expects($this->once())->method('setPrice')->with(10.00);
        $item->expects($this->once())->method('setCustomPrice')->with(0);

        $this->processor->prepare($item, $request, $candidate);
    }

    /**
     * Sets store id correctly when running in the backend area.
     */
    public function testInitSetsStoreIdInBackendArea(): void
    {
        $item = $this->getMockBuilder(Item::class)->disableOriginalConstructor()->addMethods(['setStoreId'])->getMock();
        $product = $this->createMock(Product::class);
        $request = new DataObject([
            'reset_count' => 0,
            'id' => 5,
        ]);

        $this->itemFactory->method('create')->willReturn($item);

        $this->appState->method('getAreaCode')
            ->willReturn(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);

        $storeInitial = $this->createMock(StoreInterface::class);
        $storeInitial->method('getId')->willReturn(7);

        $storeFinal = $this->createMock(StoreInterface::class);
        $storeFinal->method('getId')->willReturn(7);

        $this->storeManager->method('getStore')
            ->willReturnMap([
                [null, $storeInitial],
                [7, $storeFinal],
            ]);

        $item->expects($this->once())->method('setStoreId')->with(7);

        $resultItem = $this->processor->init($product, $request);
        $this->assertSame($item, $resultItem);
    }

    /**
     * Sets store id correctly when running in the frontend area.
     */
    public function testInitSetsStoreIdInFrontendArea(): void
    {
        $item = $this->getMockBuilder(Item::class)->disableOriginalConstructor()->addMethods(['setStoreId'])->getMock();
        $product = $this->createMock(Product::class);
        $request = new DataObject([
            'reset_count' => 0,
            'id' => 6,
        ]);

        $this->itemFactory->method('create')->willReturn($item);

        $this->appState->method('getAreaCode')->willReturn('frontend');

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(3);
        $this->storeManager->method('getStore')->willReturn($store);

        $item->expects($this->once())->method('setStoreId')->with(3);

        $resultItem = $this->processor->init($product, $request);
        $this->assertSame($item, $resultItem);
    }

    /**
     * Returns early for existing child items without resetting qty.
     */
    public function testInitDoesNotModifyExistingChildItem(): void
    {
        $item = $this->getMockBuilder(Item::class)->disableOriginalConstructor()->addMethods(['setStoreId'])->getMock();
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['getParentProductId'])
            ->getMock();
        $request = new DataObject([
            'reset_count' => 0,
            'id' => 99,
        ]);

        $this->itemFactory->method('create')->willReturn($item);
        $this->appState->method('getAreaCode')->willReturn('frontend');

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(1);
        $this->storeManager->method('getStore')->willReturn($store);

        $product->method('getParentProductId')->willReturn(123);

        $item->expects($this->once())->method('setStoreId')->with(1);

        $resultItem = $this->processor->init($product, $request);
        $this->assertSame($item, $resultItem);
    }

    /**
     * Returns early in init() when item has id and product has parent id (child).
     * Ensures the qty reset branch is not evaluated.
     */
    public function testInitReturnsEarlyWhenItemHasIdAndProductHasParent(): void
    {
        $item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->addMethods(['setStoreId'])
            ->getMock();
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['getParentProductId', 'getStickWithinParent'])
            ->getMock();
        $request = new DataObject([
            'reset_count' => 1,
            'id' => 77,
        ]);

        $this->itemFactory->method('create')->willReturn($item);
        $this->appState->method('getAreaCode')->willReturn('frontend');

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(2);
        $this->storeManager->method('getStore')->willReturn($store);

        $item->method('getId')->willReturn(77);
        $product->method('getParentProductId')->willReturn(555);
        // Because we return early, stickWithinParent should never be consulted
        $product->expects($this->never())->method('getStickWithinParent');

        $item->expects($this->once())->method('setStoreId')->with(2);

        $resultItem = $this->processor->init($product, $request);
        $this->assertSame($item, $resultItem);
    }

    /**
     * Does not set price when parent item exists and children are not calculated.
     */
    public function testPrepareDoesNotSetPriceWhenParentItemChildrenNotCalculated(): void
    {
        $item = $this->createMock(Item::class);
        $parentItem = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isChildrenCalculated'])
            ->getMock();
        $candidate = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFinalPrice'])
            ->addMethods(['getStickWithinParent', 'getCartQty', 'getParentProductId'])
            ->getMock();
        $request = new DataObject([
            'custom_price' => null,
            'reset_count' => 0,
            'id' => 22,
        ]);

        $candidate->method('getStickWithinParent')->willReturn(false);
        $candidate->method('getCartQty')->willReturn(1);
        $candidate->method('getFinalPrice')->willReturn(200.00);
        $candidate->method('getParentProductId')->willReturn(null);

        $item->method('getId')->willReturn(22);
        $item->method('getParentItem')->willReturn($parentItem);
        $parentItem->method('isChildrenCalculated')->willReturn(false);

        $item->expects($this->once())->method('addQty')->with(1);
        $item->expects($this->never())->method('setPrice');

        $this->processor->prepare($item, $request, $candidate);
    }

    /**
     * Does not reset qty when candidate is set to stick within parent.
     */
    public function testPrepareDoesNotResetQtyWhenStickWithinParentTrue(): void
    {
        $item = $this->createMock(Item::class);
        $candidate = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFinalPrice'])
            ->addMethods(['getStickWithinParent', 'getCartQty', 'getParentProductId'])
            ->getMock();
        $request = new DataObject([
            'custom_price' => 10,
            'reset_count' => 1,
            'id' => 33,
        ]);

        $candidate->method('getStickWithinParent')->willReturn(true);
        $candidate->method('getCartQty')->willReturn(5);
        $candidate->method('getFinalPrice')->willReturn(15.00);
        $candidate->method('getParentProductId')->willReturn(null);

        $item->method('getParentItem')->willReturn(null);

        $item->expects($this->never())->method('setData')->with(CartItemInterface::KEY_QTY, 0);
        $item->expects($this->once())->method('addQty')->with(5);
        $item->expects($this->once())->method('setPrice')->with(15.00);
        $item->expects($this->once())->method('setCustomPrice')->with(10);

        $this->processor->prepare($item, $request, $candidate);
    }

    /**
     * Returns the target item when merging items.
     */
    public function testMergeReturnsTarget(): void
    {
        $source = $this->createMock(Item::class);
        $target = $this->createMock(Item::class);

        $result = $this->processor->merge($source, $target);
        $this->assertSame($target, $result);
    }

    /**
     * Resets qty in init() when reset_count is set, stickWithinParent is false, and ids match.
     */
    public function testInitResetsQtyWhenResetCountAndMatchingId(): void
    {
        $item = $this->getMockBuilder(Item::class)->disableOriginalConstructor()->addMethods(['setStoreId'])->getMock();
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['getStickWithinParent', 'getParentProductId'])
            ->getMock();
        $request = new DataObject([
            'reset_count' => 1,
            'id' => null,
        ]);

        $this->itemFactory->method('create')->willReturn($item);

        $this->appState->method('getAreaCode')->willReturn('frontend');
        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(9);
        $this->storeManager->method('getStore')->willReturn($store);

        $product->method('getStickWithinParent')->willReturn(false);
        $product->method('getParentProductId')->willReturn(null);

        $item->expects($this->once())->method('setStoreId')->with(9);

        $resultItem = $this->processor->init($product, $request);
        $this->assertSame($item, $resultItem);
    }

    /**
     * Does not reset qty in init() when ids do not match.
     */
    public function testInitDoesNotResetQtyWhenIdMismatch(): void
    {
        $item = $this->getMockBuilder(Item::class)->disableOriginalConstructor()->addMethods(['setStoreId'])->getMock();
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['getStickWithinParent', 'getParentProductId'])
            ->getMock();
        $request = new DataObject([
            'reset_count' => 1,
            'id' => 55,
        ]);

        $this->itemFactory->method('create')->willReturn($item);

        $this->appState->method('getAreaCode')->willReturn('frontend');
        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(2);
        $this->storeManager->method('getStore')->willReturn($store);

        $product->method('getStickWithinParent')->willReturn(false);
        $product->method('getParentProductId')->willReturn(null);

        $item->expects($this->once())->method('setStoreId')->with(2);

        $resultItem = $this->processor->init($product, $request);
        $this->assertSame($item, $resultItem);
    }

    /**
     * Sets price in prepare() when parent exists and childrenCalculated is true.
     */
    public function testPrepareSetsPriceWhenParentItemChildrenCalculated(): void
    {
        $item = $this->createMock(Item::class);
        $parentItem = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isChildrenCalculated'])
            ->getMock();
        $candidate = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFinalPrice'])
            ->addMethods(['getStickWithinParent', 'getCartQty', 'getParentProductId'])
            ->getMock();
        $request = new DataObject([
            'custom_price' => null,
            'reset_count' => 0,
            'id' => 66,
        ]);

        $candidate->method('getStickWithinParent')->willReturn(false);
        $candidate->method('getCartQty')->willReturn(7);
        $candidate->method('getFinalPrice')->willReturn(33.00);
        $candidate->method('getParentProductId')->willReturn(null);

        $item->method('getId')->willReturn(66);
        $item->method('getParentItem')->willReturn($parentItem);
        $parentItem->method('isChildrenCalculated')->willReturn(true);

        $item->expects($this->once())->method('addQty')->with(7);
        $item->expects($this->once())->method('setPrice')->with(33.00);

        $this->processor->prepare($item, $request, $candidate);
    }
}

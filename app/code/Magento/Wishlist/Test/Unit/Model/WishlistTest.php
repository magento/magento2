<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Model;

use ArrayIterator;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as HelperProduct;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Stock\Item as StockItem;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\ItemFactory;
use Magento\Wishlist\Model\ResourceModel\Item\Collection;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResource;
use Magento\Wishlist\Model\ResourceModel\Wishlist\Collection as WishlistCollection;
use Magento\Wishlist\Model\Wishlist;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class WishlistTest extends TestCase
{
    /**
     * @var Registry|MockObject
     */
    protected $registry;

    /**
     * @var HelperProduct|MockObject
     */
    protected $productHelper;

    /**
     * @var Data|MockObject
     */
    protected $helper;

    /**
     * @var WishlistResource|MockObject
     */
    protected $resource;

    /**
     * @var WishlistCollection|MockObject
     */
    protected $collection;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var DateTime\DateTime|MockObject
     */
    protected $date;

    /**
     * @var ItemFactory|MockObject
     */
    protected $itemFactory;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $itemsFactory;

    /**
     * @var ProductFactory|MockObject
     */
    protected $productFactory;

    /**
     * @var Random|MockObject
     */
    protected $mathRandom;

    /**
     * @var DateTime|MockObject
     */
    protected $dateTime;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventDispatcher;

    /**
     * @var Wishlist
     */
    protected $wishlist;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    protected $productRepository;

    /**
     * @var Json|MockObject
     */
    protected $serializer;

    /**
     * @var StockItemRepository|MockObject
     */
    private $scopeConfig;

    /**
     * @var StockRegistryInterface|MockObject
     */
    private $stockRegistry;
    /**
     * @var StockConfigurationInterface|MockObject
     */
    private $stockConfiguration;

    protected function setUp(): void
    {
        $context = $this->createMock(Context::class);

        $this->eventDispatcher = $this->createMock(ManagerInterface::class);
        $this->registry = $this->createMock(Registry::class);
        $this->productHelper = $this->createMock(HelperProduct::class);
        $this->helper = $this->createMock(Data::class);
        $this->resource = $this->createMock(WishlistResource::class);
        $this->collection = $this->createMock(WishlistCollection::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->date = $this->createMock(DateTime\DateTime::class);
        $this->itemFactory = $this->createPartialMock(ItemFactory::class, ['create']);
        $this->itemsFactory = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->productFactory = $this->createPartialMock(ProductFactory::class, ['create']);
        $this->mathRandom = $this->createMock(Random::class);
        $this->dateTime = $this->createMock(DateTime::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->stockRegistry = $this->createMock(StockRegistryInterface::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);

        $this->serializer = $this->createMock(Json::class);

        $context->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->eventDispatcher);

        $this->stockConfiguration = $this->createMock(StockConfigurationInterface::class);

        $this->wishlist = new Wishlist(
            $context,
            $this->registry,
            $this->productHelper,
            $this->helper,
            $this->resource,
            $this->collection,
            $this->storeManager,
            $this->date,
            $this->itemFactory,
            $this->itemsFactory,
            $this->productFactory,
            $this->mathRandom,
            $this->dateTime,
            $this->productRepository,
            false,
            [],
            $this->serializer,
            $this->stockRegistry,
            $this->scopeConfig,
            $this->stockConfiguration
        );
    }

    public function testLoadByCustomerId()
    {
        $customerId = 1;
        $customerIdFieldName = 'customer_id';
        $sharingCode = 'expected_sharing_code';
        $this->eventDispatcher->expects($this->any())
            ->method('dispatch');
        $this->resource->expects($this->any())
            ->method('getCustomerIdFieldName')
            ->willReturn('test_customer_id');
        $this->resource->expects($this->once())
            ->method('load')
            ->with($this->logicalOr($this->wishlist, $customerId, $customerIdFieldName));
        $this->mathRandom->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($sharingCode);

        $this->assertInstanceOf(
            Wishlist::class,
            $this->wishlist->loadByCustomerId($customerId, true)
        );
        $this->assertEquals($customerId, $this->wishlist->getCustomerId());
        $this->assertEquals($sharingCode, $this->wishlist->getSharingCode());
    }

    /**
     * @param  int|Item|MockObject   $itemId
     * @param  DataObject|\Closure   $buyRequest
     * @param  null|array|DataObject $param
     * @throws LocalizedException
     */
    #[DataProvider('updateItemDataProvider')]
    public function testUpdateItem($itemId, $buyRequest, $param): void
    {
        $buyRequest = $buyRequest($this);
        $storeId = 1;
        $productId = 1;
        $stores = [(new DataObject())->setId($storeId)];

        $newItem = $this->prepareWishlistItem();

        $this->itemFactory->expects($this->once())->method('create')->willReturn($newItem);
        $this->productHelper->expects($this->once())->method('addParamsToBuyRequest')->willReturn($buyRequest);

        $this->storeManager->expects($this->any())->method('getStores')->willReturn($stores);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($stores[0]);

        $product = $this->createMock(Product::class);
        $product->expects($this->any())->method('getId')->willReturn($productId);
        $product->expects($this->any())->method('getStoreId')->willReturn($storeId);

        $stockItem = $this->createMock(StockItem::class);
        $stockItem->expects($this->any())->method('getIsInStock')->willReturn(true);
        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->willReturn($stockItem);

        $instanceType = $this->createMock(AbstractType::class);
        $instanceType->expects($this->once())
            ->method('processConfiguration')
            ->willReturn(
                $this->createMock(Product::class)
            );

        $newProduct = $this->createMock(Product::class);
        $newProduct->expects($this->any())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $newProduct->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($instanceType);
        $newProduct->expects($this->any())->method('getIsSalable')->willReturn(true);

        $item = $this->createMock(Item::class);
        $item->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);

        $items = $this->createMock(Collection::class);

        $items->expects($this->once())
            ->method('addWishlistFilter')
            ->willReturnSelf();
        $items->expects($this->once())
            ->method('addStoreFilter')
            ->willReturnSelf();
        $items->expects($this->once())
            ->method('setVisibilityFilter')
            ->willReturnSelf();
        $items->expects($this->once())
            ->method('getItemById')
            ->willReturn($item);
        $items->expects($this->any())
            ->method('getIterator')
            ->willReturn(new ArrayIterator([$item]));

        $this->itemsFactory->expects($this->any())
            ->method('create')
            ->willReturn($items);

        $this->productRepository->expects($this->once())
            ->method('getById')
            ->with($productId, false, $storeId)
            ->willReturn($newProduct);

        $this->assertInstanceOf(
            Wishlist::class,
            $this->wishlist->updateItem($itemId, $buyRequest, $param)
        );
    }

    /**
     * Prepare wishlist item mock.
     *
     * @return Item
     */
    private function prepareWishlistItem(): Item
    {
        $newItem = $this->createStub(Item::class);

        return $newItem;
    }

    protected function getMockForDataObject()
    {
        $dataObjectMock = $this->createMock(DataObject::class);
        $dataObjectMock->expects($this->once())
            ->method('setData')
            ->with('action', 'updateItem')
            ->willReturnSelf();
        $dataObjectMock->expects($this->once())
            ->method('getData')
            ->with('action')
            ->willReturn('updateItem');
        return $dataObjectMock;
    }

    /**
     * @return array
     */
    public static function updateItemDataProvider(): array
    {
        $dataObjectMock = static fn (self $testCase) => $testCase->getMockForDataObject();
        return [
            '0' => [1, $dataObjectMock, null]
        ];
    }

    /**
     * @param bool   $getIsSalable
     * @param bool   $isShowOutOfStock
     * @param string $throwException
     */
    #[DataProvider('addNewItemDataProvider')]
    public function testAddNewItem(bool $getIsSalable, bool $isShowOutOfStock, string $throwException): void
    {
        if ($throwException) {
            $this->expectExceptionMessage($throwException);
        }
        $this->stockConfiguration->method('isShowOutOfStock')->willReturn($isShowOutOfStock);
        $productId = 1;
        $storeId = 1;
        $buyRequest = json_encode(
            [
                'number' => 42,
                'string' => 'string_value',
                'boolean' => true,
                'collection' => [1, 2, 3],
                'product' => 1,
                'form_key' => 'abc'
            ]
        );
        $result = 'product';

        $instanceType = $this->createMock(AbstractType::class);
        $instanceType->method('processConfiguration')
            ->willReturn('product');

        $productMock = $this->createProductMockForAddNewItem($productId, $storeId, $instanceType, $getIsSalable);

        $this->productRepository->expects($this->once())
            ->method('getById')
            ->with($productId, false, $storeId)
            ->willReturn($productMock);

        $this->serializer->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $stockItem = $this->createMock(StockItem::class);
        $stockItem->expects($this->any())->method('getIsInStock')->willReturn(true);

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->willReturn($stockItem);

        $this->assertEquals($result, $this->wishlist->addNewItem($productMock, $buyRequest));
    }

    /**
     * @return array[]
     */
    public static function addNewItemDataProvider(): array
    {
        return [
            [false, false, 'Cannot add product without stock to wishlist'],
            [false, true, ''],
            [true, false, ''],
            [true, true, ''],
        ];
    }

    private function createProductMockForAddNewItem($productId, $storeId, $instanceType, $getIsSalable)
    {
        $product = $this->createPartialMock(Product::class, ['getId', 'getStoreId', 'getTypeInstance', 'getIsSalable']);
        $product->method('getId')->willReturn($productId);
        $product->method('getStoreId')->willReturn($storeId);
        $product->method('getTypeInstance')->willReturn($instanceType);
        $product->method('getIsSalable')->willReturn($getIsSalable);
        return $product;
    }
}

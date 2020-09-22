<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventDispatcher = $this->getMockBuilder(ManagerInterface::class)
            ->getMock();
        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productHelper = $this->getMockBuilder(HelperProduct::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder(WishlistResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collection = $this->getMockBuilder(WishlistCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        $this->date = $this->getMockBuilder(DateTime\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemFactory = $this->getMockBuilder(ItemFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->itemsFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->productFactory = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->mathRandom = $this->getMockBuilder(Random::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTime = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepository = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->stockRegistry = $this->getMockForAbstractClass(StockRegistryInterface::class);
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->serializer = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

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
            ->method('getCustomerIdFieldName');
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
     * @param int|Item|MockObject $itemId
     * @param DataObject|MockObject $buyRequest
     * @param null|array|DataObject $param
     * @throws LocalizedException
     *
     * @dataProvider updateItemDataProvider
     */
    public function testUpdateItem($itemId, $buyRequest, $param): void
    {
        $storeId = 1;
        $productId = 1;
        $stores = [(new DataObject())->setId($storeId)];

        $newItem = $this->prepareWishlistItem();

        $this->itemFactory->expects($this->once())->method('create')->willReturn($newItem);
        $this->productHelper->expects($this->once())->method('addParamsToBuyRequest')->willReturn($buyRequest);

        $this->storeManager->expects($this->any())->method('getStores')->willReturn($stores);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($stores[0]);

        $product = $this->getMockBuilder(
            Product::class
        )->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->any())->method('getId')->willReturn($productId);
        $product->expects($this->any())->method('getStoreId')->willReturn($storeId);

        $stockItem = $this->getMockBuilder(StockItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stockItem->expects($this->any())->method('getIsInStock')->willReturn(true);
        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->willReturn($stockItem);

        $instanceType = $this->getMockBuilder(AbstractType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $instanceType->expects($this->once())
            ->method('processConfiguration')
            ->willReturn(
                $this->getMockBuilder(Product::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );

        $newProduct = $this->getMockBuilder(
            Product::class
        )->disableOriginalConstructor()
            ->getMock();
        $newProduct->expects($this->any())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $newProduct->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($instanceType);
        $newProduct->expects($this->any())->method('getIsSalable')->willReturn(true);

        $item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $item->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);

        $items = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

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
     * @return MockObject
     */
    private function prepareWishlistItem(): MockObject
    {
        $newItem = $this->getMockBuilder(Item::class)
            ->setMethods(
                ['setProductId', 'setWishlistId', 'setStoreId', 'setOptions', 'setProduct', 'setQty', 'getItem', 'save']
            )
            ->disableOriginalConstructor()
            ->getMock();
        $newItem->expects($this->any())->method('setProductId')->willReturnSelf();
        $newItem->expects($this->any())->method('setWishlistId')->willReturnSelf();
        $newItem->expects($this->any())->method('setStoreId')->willReturnSelf();
        $newItem->expects($this->any())->method('setOptions')->willReturnSelf();
        $newItem->expects($this->any())->method('setProduct')->willReturnSelf();
        $newItem->expects($this->any())->method('setQty')->willReturnSelf();
        $newItem->expects($this->any())->method('getItem')->willReturn(2);
        $newItem->expects($this->any())->method('save')->willReturnSelf();

        return $newItem;
    }

    /**
     * @return array
     */
    public function updateItemDataProvider(): array
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

        return [
            '0' => [1, $dataObjectMock, null]
        ];
    }

    /**
     * @param bool $getIsSalable
     * @param bool $isShowOutOfStock
     * @param string $throwException
     *
     * @dataProvider addNewItemDataProvider
     */
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

        $instanceType = $this->getMockBuilder(AbstractType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $instanceType->method('processConfiguration')
            ->willReturn('product');

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'hasWishlistStoreId', 'getStoreId', 'getTypeInstance', 'getIsSalable'])
            ->getMock();
        $productMock->method('getId')
            ->willReturn($productId);
        $productMock->method('hasWishlistStoreId')
            ->willReturn(false);
        $productMock->method('getStoreId')
            ->willReturn($storeId);
        $productMock->method('getTypeInstance')
            ->willReturn($instanceType);
        $productMock->expects($this->any())
            ->method('getIsSalable')
            ->willReturn($getIsSalable);

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

        $stockItem = $this->getMockBuilder(
            StockItem::class
        )->disableOriginalConstructor()
            ->getMock();
        $stockItem->expects($this->any())->method('getIsInStock')->willReturn(true);

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->willReturn($stockItem);

        $this->assertEquals($result, $this->wishlist->addNewItem($productMock, $buyRequest));
    }

    /**
     * @return array[]
     */
    public function addNewItemDataProvider(): array
    {
        return [
            [false, false, 'Cannot add product without stock to wishlist'],
            [false, true, ''],
            [true, false, ''],
            [true, true, ''],
        ];
    }
}

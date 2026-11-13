<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ProductRepository;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\Tierprice as TierPriceBackend;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Magento\Catalog\Model\ProductRepository\ListItemsPreloader;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice as TierPriceResource;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ListItemsPreloaderTest extends TestCase
{
    use MockCreationTrait;

    private const LINK_FIELD = 'row_id';

    /**
     * @var AdapterInterface|MockObject
     */
    private $websiteConnection;

    /**
     * @var AdapterInterface|MockObject
     */
    private $tierPriceConnection;

    /**
     * @var AdapterInterface|MockObject
     */
    private $galleryConnection;

    /**
     * @var TierPriceResource|MockObject
     */
    private $tierPriceResource;

    /**
     * @var TierPriceBackend|MockObject
     */
    private $tierPriceBackend;

    /**
     * @var Attribute|MockObject
     */
    private $tierPriceAttribute;

    /**
     * @var Gallery|MockObject
     */
    private $galleryResource;

    /**
     * @var ProductResource|MockObject
     */
    private $productResource;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var ListItemsPreloader
     */
    private $preloader;

    protected function setUp(): void
    {
        $this->websiteConnection = $this->createConnectionMock();
        $resourceConnection = $this->createStub(ResourceConnection::class);
        $resourceConnection->method('getConnection')->willReturn($this->websiteConnection);
        $resourceConnection->method('getTableName')->willReturnArgument(0);

        $metadata = $this->createStub(EntityMetadataInterface::class);
        $metadata->method('getLinkField')->willReturn(self::LINK_FIELD);
        $metadataPool = $this->createStub(MetadataPool::class);
        $metadataPool->method('getMetadata')->willReturnMap([[ProductInterface::class, $metadata]]);

        $this->tierPriceConnection = $this->createConnectionMock();
        $this->tierPriceResource = $this->createMock(TierPriceResource::class);
        $this->tierPriceResource->method('getConnection')->willReturn($this->tierPriceConnection);
        $this->tierPriceBackend = $this->createMock(TierPriceBackend::class);
        $this->tierPriceBackend->method('getResource')->willReturn($this->tierPriceResource);
        $this->tierPriceAttribute = $this->createStub(Attribute::class);
        $this->tierPriceAttribute->method('getBackend')->willReturn($this->tierPriceBackend);

        $galleryAttribute = $this->createStub(Attribute::class);
        $galleryAttribute->method('getAttributeId')->willReturn(90);
        $this->galleryConnection = $this->createConnectionMock();
        $this->galleryResource = $this->createMock(Gallery::class);
        $this->galleryResource->method('getConnection')->willReturn($this->galleryConnection);

        $this->productResource = $this->createStub(ProductResource::class);
        $this->productResource->method('getAttribute')->willReturnMap(
            [
                ['tier_price', $this->tierPriceAttribute],
                ['media_gallery', $galleryAttribute],
            ]
        );

        $this->storeManager = $this->createStub(StoreManagerInterface::class);

        $this->preloader = new ListItemsPreloader(
            $resourceConnection,
            $metadataPool,
            $this->storeManager,
            $this->galleryResource
        );
    }

    public function testExecuteWithoutProductsDoesNotQuery(): void
    {
        $this->websiteConnection->expects($this->never())->method('fetchAll');
        $this->tierPriceConnection->expects($this->never())->method('fetchAll');
        $this->galleryConnection->expects($this->never())->method('fetchAll');

        $this->assertSame([], $this->preloader->execute([]));
    }

    public function testExecuteSetsWebsiteIdsWithOneQuery(): void
    {
        $withRows = $this->createProduct(1, 1, []);
        $withoutRows = $this->createProduct(2, 1, []);
        $alreadyLoaded = $this->createProduct(3, 1, [], ['5']);
        $this->stubTierPricesAndGallery();

        $this->websiteConnection->expects($this->once())
            ->method('fetchAll')
            ->willReturn(
                [
                    ['product_id' => '1', 'website_id' => '1'],
                    ['product_id' => '1', 'website_id' => '2'],
                ]
            );

        $this->preloader->execute([1 => $withRows, 2 => $withoutRows, 3 => $alreadyLoaded]);

        $this->assertSame(['1', '2'], $withRows->getExtensionAttributes()->getWebsiteIds());
        $this->assertSame([], $withoutRows->getExtensionAttributes()->getWebsiteIds());
        $this->assertSame(['5'], $alreadyLoaded->getExtensionAttributes()->getWebsiteIds());
    }

    public function testExecuteSetsTierPricesWithOneQueryForGlobalScope(): void
    {
        $first = $this->createProduct(1, 1, [self::LINK_FIELD => 11]);
        $second = $this->createProduct(2, 0, [self::LINK_FIELD => 12]);
        $alreadyLoaded = $this->createProduct(3, 1, [self::LINK_FIELD => 13, 'tier_price' => []]);
        $this->websiteConnection->method('fetchAll')->willReturn([]);
        $this->galleryResource->method('createBatchBaseSelect')->willReturn($this->createSelectMock());
        $this->galleryConnection->method('fetchAll')->willReturn([]);

        $this->tierPriceAttribute->method('isScopeGlobal')->willReturn(true);
        $select = $this->createMock(Select::class);
        $select->expects($this->once())->method('where')->with('row_id IN (?)', [11, 12])->willReturnSelf();
        $select->expects($this->once())->method('order')->with('qty')->willReturnSelf();
        $this->tierPriceResource->expects($this->once())->method('getSelect')->with(0)->willReturn($select);
        $firstRow = ['price_id' => '7', 'product_id' => '11', 'price_qty' => '2.0000'];
        $this->tierPriceConnection->expects($this->once())->method('fetchAll')->willReturn([$firstRow]);

        $priceData = [];
        $this->tierPriceBackend->expects($this->exactly(2))
            ->method('setPriceData')
            ->willReturnCallback(
                function (Product $product, array $rows) use (&$priceData): void {
                    $priceData[$product->getId()] = $rows;
                }
            );

        $this->preloader->execute([1 => $first, 2 => $second, 3 => $alreadyLoaded]);

        $this->assertSame([1 => [$firstRow], 2 => []], $priceData);
    }

    public function testExecuteResolvesTierPriceWebsiteLikeSingleProductLoad(): void
    {
        $adminStoreProduct = $this->createProduct(1, 0, [self::LINK_FIELD => 11]);
        $storeProduct = $this->createProduct(2, 4, [self::LINK_FIELD => 12]);
        $this->websiteConnection->method('fetchAll')->willReturn([]);
        $this->galleryResource->method('createBatchBaseSelect')->willReturn($this->createSelectMock());
        $this->galleryConnection->method('fetchAll')->willReturn([]);

        $this->tierPriceAttribute->method('isScopeGlobal')->willReturn(false);
        $store = $this->createStub(StoreInterface::class);
        $store->method('getWebsiteId')->willReturn('3');
        $this->storeManager->method('getStore')->willReturnMap([[4, $store]]);
        $requestedWebsites = [];
        $this->tierPriceResource->expects($this->exactly(2))
            ->method('getSelect')
            ->willReturnCallback(
                function (?int $websiteId) use (&$requestedWebsites): Select {
                    $requestedWebsites[] = $websiteId;
                    return $this->createSelectMock();
                }
            );
        $this->tierPriceConnection->method('fetchAll')->willReturn([]);

        $this->preloader->execute([1 => $adminStoreProduct, 2 => $storeProduct]);

        $this->assertSame([null, 3], $requestedWebsites);
    }

    public function testExecuteReturnsGalleryEntriesAsReadArguments(): void
    {
        $first = $this->createProduct(1, 1, [self::LINK_FIELD => 11]);
        $withoutImages = $this->createProduct(2, 1, [self::LINK_FIELD => 12]);
        $withDuplicates = $this->createProduct(3, 1, [self::LINK_FIELD => 13]);
        $this->websiteConnection->method('fetchAll')->willReturn([]);
        $this->tierPriceAttribute->method('isScopeGlobal')->willReturn(true);
        $this->tierPriceResource->method('getSelect')->willReturn($this->createSelectMock());
        $this->tierPriceConnection->method('fetchAll')->willReturn([]);

        $select = $this->createMock(Select::class);
        $select->expects($this->once())->method('where')->with('entity.row_id IN (?)', [11, 12, 13])->willReturnSelf();
        $this->galleryResource->expects($this->once())
            ->method('createBatchBaseSelect')
            ->with(1, 90)
            ->willReturn($select);
        $firstRows = [
            ['value_id' => '1', 'file' => '/a.jpg', self::LINK_FIELD => '11'],
            ['value_id' => '2', 'file' => '/b.jpg', self::LINK_FIELD => '11'],
        ];
        $duplicateRows = [
            ['value_id' => '3', 'file' => '/c.jpg', self::LINK_FIELD => '13'],
            ['value_id' => '4', 'file' => '/c.jpg', self::LINK_FIELD => '13'],
        ];
        $this->galleryConnection->expects($this->once())
            ->method('fetchAll')
            ->willReturn([...$firstRows, ...$duplicateRows]);

        $arguments = $this->preloader->execute([1 => $first, 2 => $withoutImages, 3 => $withDuplicates]);

        $this->assertSame(
            [
                1 => [GalleryReadHandler::PRELOADED_ENTRIES => $firstRows],
                2 => [GalleryReadHandler::PRELOADED_ENTRIES => []],
            ],
            $arguments
        );
    }

    /**
     * Stub the tier price and gallery queries with empty results
     *
     * @return void
     */
    private function stubTierPricesAndGallery(): void
    {
        $this->tierPriceAttribute->method('isScopeGlobal')->willReturn(true);
        $this->tierPriceResource->method('getSelect')->willReturn($this->createSelectMock());
        $this->tierPriceConnection->method('fetchAll')->willReturn([]);
        $this->galleryResource->method('createBatchBaseSelect')->willReturn($this->createSelectMock());
        $this->galleryConnection->method('fetchAll')->willReturn([]);
    }

    /**
     * Create a product mock keeping real data storage
     *
     * @param int $id
     * @param int $storeId
     * @param array $data
     * @param array|null $websiteIds
     * @return Product|MockObject
     */
    private function createProduct(int $id, int $storeId, array $data, ?array $websiteIds = null): Product
    {
        $extensionAttributes = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            ['getWebsiteIds', 'setWebsiteIds']
        );
        $extensionAttributes->method('getWebsiteIds')->willReturnCallback(
            function () use (&$websiteIds): ?array {
                return $websiteIds;
            }
        );
        $extensionAttributes->method('setWebsiteIds')->willReturnCallback(
            function (?array $value) use (&$websiteIds, $extensionAttributes): ProductExtensionInterface {
                $websiteIds = $value;
                return $extensionAttributes;
            }
        );

        $product = $this->createPartialMock(
            Product::class,
            ['getId', 'getStoreId', 'getResource', 'getExtensionAttributes', 'setExtensionAttributes']
        );
        $product->setData($data);
        $product->method('getId')->willReturn($id);
        $product->method('getStoreId')->willReturn($storeId);
        $product->method('getResource')->willReturn($this->productResource);
        $product->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $product->method('setExtensionAttributes')->willReturnSelf();

        return $product;
    }

    /**
     * Create a connection mock whose select() returns a fluent select mock
     *
     * @return AdapterInterface|MockObject
     */
    private function createConnectionMock(): AdapterInterface
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('select')->willReturnCallback(fn () => $this->createSelectMock());

        return $connection;
    }

    /**
     * Create a fluent select mock
     *
     * @return Select|MockObject
     */
    private function createSelectMock(): Select
    {
        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->method('order')->willReturnSelf();

        return $select;
    }
}

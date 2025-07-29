<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Model\ResourceModel\Catalog\Batch;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Sitemap\Helper\Data as SitemapHelper;
use Magento\Sitemap\Model\ResourceModel\Catalog\Batch\Product as BatchProduct;
use Magento\Sitemap\Model\ResourceModel\Catalog\ProductSelectBuilder;
use Magento\Sitemap\Model\Source\Product\Image\IncludeImage;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Batch Product resource model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends TestCase
{
    /**
     * @var BatchProduct
     */
    private $model;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var SitemapHelper|MockObject
     */
    private $sitemapHelperMock;

    /**
     * @var ProductResource|MockObject
     */
    private $productResourceMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Visibility|MockObject
     */
    private $productVisibilityMock;

    /**
     * @var Status|MockObject
     */
    private $productStatusMock;

    /**
     * @var Gallery|MockObject
     */
    private $mediaGalleryResourceModelMock;

    /**
     * @var ReadHandler|MockObject
     */
    private $mediaGalleryReadHandlerMock;

    /**
     * @var UrlBuilder|MockObject
     */
    private $imageUrlBuilderMock;

    /**
     * @var ProductSelectBuilder|MockObject
     */
    private $productSelectBuilderMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->sitemapHelperMock = $this->createMock(SitemapHelper::class);
        $this->productResourceMock = $this->createMock(ProductResource::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->productVisibilityMock = $this->createMock(Visibility::class);
        $this->productStatusMock = $this->createMock(Status::class);
        $this->mediaGalleryResourceModelMock = $this->createMock(Gallery::class);
        $this->mediaGalleryReadHandlerMock = $this->createMock(ReadHandler::class);
        $this->imageUrlBuilderMock = $this->createMock(UrlBuilder::class);
        $this->productSelectBuilderMock = $this->createMock(ProductSelectBuilder::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->selectMock = $this->createMock(Select::class);
        $this->storeMock = $this->createMock(Store::class);

        $resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->contextMock->expects($this->any())
            ->method('getResources')
            ->willReturn($resourceMock);

        $this->model = $this->getMockBuilder(BatchProduct::class)
            ->setConstructorArgs([
                $this->contextMock,
                $this->sitemapHelperMock,
                $this->productResourceMock,
                $this->storeManagerMock,
                $this->productVisibilityMock,
                $this->productStatusMock,
                $this->mediaGalleryResourceModelMock,
                $this->mediaGalleryReadHandlerMock,
                null,
                $this->imageUrlBuilderMock,
                $this->productSelectBuilderMock,
            ])
            ->onlyMethods(['getMainTable', 'getIdFieldName'])
            ->getMock();

        $this->model->expects($this->any())
            ->method('getMainTable')
            ->willReturn('catalog_product_entity');

        $this->model->expects($this->any())
            ->method('getIdFieldName')
            ->willReturn('entity_id');
    }

    /**
     * Test getCollection returns false when store not found
     */
    public function testGetCollectionReturnsFalseWhenStoreNotFound(): void
    {
        $storeId = 1;

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn(null);

        $result = $this->model->getCollection($storeId);

        if ($result instanceof \Generator) {
            $items = iterator_to_array($result);
            $this->assertEmpty($items);
        } else {
            $this->assertFalse($result);
        }
    }

    /**
     * Test getCollection returns generator that yields batches
     */
    public function testGetCollectionReturnsGenerator(): void
    {
        $storeId = 1;

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($this->storeMock);

        $this->setupSelectMocks();

        $rows = [
            ['entity_id' => 1, 'url' => 'product1.html'],
            ['entity_id' => 2, 'url' => 'product2.html'],
            ['entity_id' => 3, 'url' => 'product3.html'],
        ];

        $statementMock = $this->createMock(\Zend_Db_Statement_Interface::class);
        $statementMock->expects($this->exactly(4))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                $rows[0],
                $rows[1],
                $rows[2],
                false
            );

        $this->connectionMock->expects($this->once())
            ->method('query')
            ->with($this->selectMock)
            ->willReturn($statementMock);

        $result = $this->model->getCollection($storeId);

        $this->assertInstanceOf(\Generator::class, $result);

        $allProducts = [];
        foreach ($result as $product) {
            $this->assertInstanceOf(DataObject::class, $product);
            $allProducts[$product->getId()] = $product->toArray();
        }

        $this->assertCount(3, $allProducts);
        $this->assertArrayHasKey(1, $allProducts);
        $this->assertArrayHasKey(2, $allProducts);
        $this->assertArrayHasKey(3, $allProducts);
    }

    /**
     * Test getCollection handles batches with valid countable objects
     */
    public function testGetCollectionHandlesCountable(): void
    {
        $storeId = 1;

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($this->storeMock);

        $this->setupSelectMocks();

        $rows = [
            ['entity_id' => 1, 'url' => 'product1.html'],
            ['entity_id' => 2, 'url' => 'product2.html'],
        ];

        $statementMock = $this->createMock(\Zend_Db_Statement_Interface::class);
        $statementMock->expects($this->exactly(3))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                $rows[0],
                $rows[1],
                false
            );

        $this->connectionMock->expects($this->once())
            ->method('query')
            ->with($this->selectMock)
            ->willReturn($statementMock);

        $result = $this->model->getCollection($storeId);

        $this->assertInstanceOf(\Generator::class, $result);

        $allProducts = [];
        foreach ($result as $product) {
            $this->assertInstanceOf(DataObject::class, $product);
            $productArray = $product->toArray();
            $this->assertIsArray($productArray);
            $allProducts[] = $productArray;
        }

        $this->assertCount(2, $allProducts);
    }

    /**
     * Test getCollection handles null values gracefully
     */
    public function testGetCollectionHandlesNullValues(): void
    {
        $storeId = 1;

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($this->storeMock);

        $this->setupSelectMocks();

        $rows = [
            ['entity_id' => 1, 'url' => null],
            ['entity_id' => 2, 'url' => 'product2.html'],
        ];

        $statementMock = $this->createMock(\Zend_Db_Statement_Interface::class);
        $statementMock->expects($this->exactly(3))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                $rows[0],
                $rows[1],
                false
            );

        $this->connectionMock->expects($this->once())
            ->method('query')
            ->with($this->selectMock)
            ->willReturn($statementMock);

        $result = $this->model->getCollection($storeId);

        $this->assertInstanceOf(\Generator::class, $result);

        foreach ($result as $product) {
            $this->assertInstanceOf(DataObject::class, $product);
            $this->assertNotNull($product->getId());
            $this->assertNotNull($product->toArray());
        }
    }

    /**
     * Test batch processing with cleanup
     */
    public function testBatchProcessingWithCleanup(): void
    {
        $storeId = 1;

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($this->storeMock);

        $this->setupSelectMocks();

        $rows = [];
        for ($i = 1; $i <= 10001; $i++) {
            $rows[] = ['entity_id' => $i, 'url' => "product{$i}.html"];
        }

        $statementMock = $this->createMock(\Zend_Db_Statement_Interface::class);

        $fetchReturns = array_merge($rows, [false]);
        $statementMock->expects($this->exactly(count($fetchReturns)))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(...$fetchReturns);

        $this->connectionMock->expects($this->once())
            ->method('query')
            ->with($this->selectMock)
            ->willReturn($statementMock);

        $result = $this->model->getCollection($storeId);
        $this->assertInstanceOf(\Generator::class, $result);

        $totalProducts = 0;
        foreach ($result as $product) {
            $this->assertInstanceOf(DataObject::class, $product);
            $this->assertNotNull($product->getId());
            $totalProducts++;
        }

        $this->assertEquals(10001, $totalProducts);
    }

    /**
     * Test product images loading with INCLUDE_ALL policy
     */
    public function testLoadProductImagesWithIncludeAll(): void
    {
        $storeId = 1;
        $productId = 1;

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($this->storeMock);

        $this->storeManagerMock->expects($this->once())
            ->method('setCurrentStore')
            ->with($storeId);

        $this->sitemapHelperMock->expects($this->any())
            ->method('getProductImageIncludePolicy')
            ->with($storeId)
            ->willReturn(IncludeImage::INCLUDE_ALL);

        $this->setupSelectMocks();

        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())
            ->method('getId')
            ->willReturn(85);

        $this->mediaGalleryReadHandlerMock->expects($this->any())
            ->method('getAttribute')
            ->willReturn($attributeMock);

        $gallery = [
            ['file' => '/image1.jpg', 'label' => 'Image 1', 'label_default' => 'Default 1'],
            ['file' => '/image2.jpg', 'label' => null, 'label_default' => 'Default 2'],
        ];

        $this->mediaGalleryResourceModelMock->expects($this->once())
            ->method('loadProductGalleryByAttributeId')
            ->willReturn($gallery);

        $this->imageUrlBuilderMock->expects($this->exactly(3))
            ->method('getUrl')
            ->willReturnCallback(function ($image) {
                return 'https://example.com/media/catalog/product' . $image;
            });

        $row = [
            'entity_id' => $productId,
            'url' => 'product.html',
            'name' => 'Test Product',
            'thumbnail' => '/thumb.jpg'
        ];

        $statementMock = $this->createMock(\Zend_Db_Statement_Interface::class);
        $statementMock->expects($this->once())
            ->method('fetch')
            ->willReturnOnConsecutiveCalls($row, false);

        $this->connectionMock->expects($this->once())
            ->method('query')
            ->with($this->selectMock)
            ->willReturn($statementMock);

        $result = $this->model->getCollection($storeId);

        $product = null;
        foreach ($result as $item) {
            if ($item->getId() === $productId) {
                $product = $item;
                break;
            }
        }

        $this->assertNotNull($product);
        $this->assertInstanceOf(DataObject::class, $product);
        $this->assertEquals($productId, $product->getId());
        $this->assertInstanceOf(DataObject::class, $product->getImages());
        $this->assertCount(2, $product->getImages()->getCollection());
    }

    /**
     * Test getCollection returns generator that yields batches with DataObject conversion
     */
    public function testGetCollectionHandlesDataObject(): void
    {
        $storeId = 1;

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($this->storeMock);

        $this->setupSelectMocks();

        $rows = [
            ['entity_id' => 1, 'url' => 'product1.html'],
            ['entity_id' => 2, 'url' => 'product2.html'],
        ];

        $statementMock = $this->createMock(\Zend_Db_Statement_Interface::class);
        $statementMock->expects($this->exactly(3))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                $rows[0],
                $rows[1],
                false
            );

        $this->connectionMock->expects($this->once())
            ->method('query')
            ->with($this->selectMock)
            ->willReturn($statementMock);

        $result = $this->model->getCollection($storeId);

        $this->assertInstanceOf(\Generator::class, $result);

        foreach ($result as $product) {
            $this->assertInstanceOf(DataObject::class, $product);
            $this->assertIsArray($product->toArray());
        }
    }

    /**
     * Setup mocks for select building
     */
    private function setupSelectMocks(): void
    {
        $this->productSelectBuilderMock->expects($this->once())
            ->method('execute')
            ->willReturn($this->selectMock);

        $this->productResourceMock->expects($this->any())
            ->method('getLinkField')
            ->willReturn('entity_id');

        $this->productVisibilityMock->expects($this->once())
            ->method('getVisibleInSiteIds')
            ->willReturn([2, 3, 4]);

        $this->productStatusMock->expects($this->once())
            ->method('getVisibleStatusIds')
            ->willReturn([1]);

        $attributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEntityTypeId', 'getId', 'getBackendType', 'getBackend'])
            ->addMethods(['getIsGlobal'])
            ->getMock();

        $backendMock = $this->createMock(AbstractBackend::class);

        $attributeMock->expects($this->any())
            ->method('getEntityTypeId')
            ->willReturn(4);
        $attributeMock->expects($this->any())
            ->method('getId')
            ->willReturn(45);
        $attributeMock->expects($this->any())
            ->method('getBackendType')
            ->willReturn('varchar');
        $attributeMock->expects($this->any())
            ->method('getIsGlobal')
            ->willReturn(1);
        $attributeMock->expects($this->any())
            ->method('getBackend')
            ->willReturn($backendMock);

        $backendMock->expects($this->any())
            ->method('getTable')
            ->willReturn('catalog_product_entity_varchar');

        $this->productResourceMock->expects($this->any())
            ->method('getAttribute')
            ->willReturn($attributeMock);

        $this->selectMock->expects($this->any())
            ->method('where')
            ->willReturnSelf();
        $this->selectMock->expects($this->any())
            ->method('joinLeft')
            ->willReturnSelf();
        $this->selectMock->expects($this->any())
            ->method('columns')
            ->willReturnSelf();

        $this->connectionMock->expects($this->any())
            ->method('quoteInto')
            ->willReturnCallback(function ($text, $value) {
                return str_replace('?', (string)$value, $text);
            });

        $this->connectionMock->expects($this->any())
            ->method('getCheckSql')
            ->willReturnCallback(function ($condition, $true, $false) {
                return "IF({$condition}, {$true}, {$false})";
            });
    }
}

<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Model\ResourceModel\Catalog;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Sitemap\Helper\Data as SitemapHelper;
use Magento\Sitemap\Model\ResourceModel\Catalog\Product;
use Magento\Sitemap\Model\ResourceModel\Catalog\ProductSelectBuilder;
use Magento\Sitemap\Model\SitemapConfigReaderInterface;
use Magento\Sitemap\Model\Source\Product\Image\IncludeImage;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Product resource model optimization
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends TestCase
{
    /**
     * @var Product|MockObject
     */
    private Product|MockObject $model;

    /**
     * @var Gallery|MockObject
     */
    private Gallery|MockObject $mediaGalleryResourceModelMock;

    /**
     * @var SitemapConfigReaderInterface|MockObject
     */
    private SitemapConfigReaderInterface|MockObject $sitemapConfigReaderMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private AdapterInterface|MockObject $connectionMock;

    protected function setUp(): void
    {
        // Create essential mocks
        $contextMock = $this->createMock(Context::class);
        $this->mediaGalleryResourceModelMock = $this->createMock(Gallery::class);
        $this->sitemapConfigReaderMock = $this->createMock(SitemapConfigReaderInterface::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);

        // Setup resource connection
        $resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $resourceMock->method('getConnection')->willReturn($this->connectionMock);
        $contextMock->method('getResources')->willReturn($resourceMock);

        // Create product model with minimal mocking
        $this->model = $this->getMockBuilder(Product::class)
            ->setConstructorArgs([
                $contextMock,
                $this->createMock(SitemapHelper::class),
                $this->createConfiguredMock(ProductResource::class, ['getLinkField' => 'entity_id']),
                $this->createConfiguredMock(StoreManagerInterface::class, [
                    'getStore' => $this->createConfiguredMock(Store::class, ['getId' => 1])
                ]),
                $this->createConfiguredMock(Visibility::class, ['getVisibleInSiteIds' => [1, 2, 3, 4]]),
                $this->createConfiguredMock(Status::class, ['getVisibleStatusIds' => [1]]),
                $this->mediaGalleryResourceModelMock,
                $this->createConfiguredMock(ReadHandler::class, [
                    'getAttribute' => $this->createConfiguredMock(Attribute::class, ['getId' => 123])
                ]),
                $this->createMock(Config::class),
                null, null, null, null,
                $this->createMock(UrlBuilder::class),
                $this->createConfiguredMock(ProductSelectBuilder::class, [
                    'execute' => $this->createMock(Select::class)
                ]),
                $this->sitemapConfigReaderMock
            ])
            ->onlyMethods(
                [
                    'getMainTable',
                    'getIdFieldName',
                    'getConnection',
                    '_addFilter',
                    '_joinAttribute',
                    'prepareSelectStatement'
                ]
            )
            ->getMock();

        // Setup model behavior
        $this->model->method('getMainTable')->willReturn('catalog_product_entity');
        $this->model->method('getIdFieldName')->willReturn('entity_id');
        $this->model->method('getConnection')->willReturn($this->connectionMock);
        $this->model->method('prepareSelectStatement')->willReturnArgument(0);
        $this->model->method('_addFilter')->willReturnSelf();
        $this->model->method('_joinAttribute')->willReturnSelf();

        // Setup select mock chain
        $selectMock = $this->createMock(Select::class);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();
        $selectMock->method('joinInner')->willReturnSelf();
        $selectMock->method('joinLeft')->willReturnSelf();
        $selectMock->method('order')->willReturnSelf();
        $selectMock->method('group')->willReturnSelf();
        $this->connectionMock->method('select')->willReturn($selectMock);
        $this->connectionMock->method('fetchAll')->willReturn([]);
    }

    /**
     * Test that batch image loading is used instead of N+1 queries
     *
     * @dataProvider productCountDataProvider
     */
    public function testGetCollectionUsesBatchImageLoading(int $productCount, int $expectedBatchCalls): void
    {
        // Generate product data
        $productData = [];
        for ($i = 1; $i <= $productCount; $i++) {
            $productData[] = ['entity_id' => $i, 'updated_at' => '2023-01-01 00:00:00'];
        }
        $productData[] = false;

        // Setup query mock
        $queryMock = $this->createMock(\Zend_Db_Statement_Interface::class);
        $queryMock->method('fetch')->willReturnOnConsecutiveCalls(...$productData);
        $this->connectionMock->method('query')->willReturn($queryMock);

        // Enable image loading
        $this->sitemapConfigReaderMock->method('getProductImageIncludePolicy')
            ->willReturn(IncludeImage::INCLUDE_ALL);

        // CORE TEST: Verify batch loading instead of N+1 queries
        $this->mediaGalleryResourceModelMock->expects($this->exactly($expectedBatchCalls))
            ->method('createBatchBaseSelect')
            ->willReturn($this->createMock(Select::class));

        $this->mediaGalleryResourceModelMock->expects($this->never())
            ->method('loadProductGalleryByAttributeId');

        // Execute test
        $result = $this->model->getCollection(1);
        $this->assertCount($productCount, $result);
    }

    public function productCountDataProvider(): array
    {
        return [
            '0 products' => [0, 0],
            '500 products' => [500, 1],
            '1000 products' => [1000, 1],
            '1001 products' => [1001, 2],
            '2500 products' => [2500, 3],
        ];
    }
}

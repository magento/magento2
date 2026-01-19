<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Model\Batch;

use Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\File\WriteInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Sitemap\Helper\Data;
use Magento\Sitemap\Model\Batch\Sitemap;
use Magento\Sitemap\Model\ItemProvider\Category;
use Magento\Sitemap\Model\ItemProvider\CmsPage;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\ItemProvider\ProductConfigReader;
use Magento\Sitemap\Model\ItemProvider\StoreUrl;
use Magento\Sitemap\Model\ResourceModel\Catalog\Batch\Product;
use Magento\Sitemap\Model\ResourceModel\Catalog\Batch\ProductFactory;
use Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory;
use Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory as BaseProductFactory;
use Magento\Sitemap\Model\ResourceModel\Cms\PageFactory;
use Magento\Sitemap\Model\ResourceModel\Sitemap as SitemapResource;
use Magento\Sitemap\Model\SitemapConfigReaderInterface;
use Magento\Sitemap\Model\SitemapItemInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class SitemapTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Sitemap|MockObject
     */
    private $sitemap;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var Registry|MockObject
     */
    private $registry;

    /**
     * @var Escaper|MockObject
     */
    private $escaper;

    /**
     * @var Data|MockObject
     */
    private $sitemapData;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var CategoryFactory|MockObject
     */
    private $categoryFactory;

    /**
     * @var BaseProductFactory|MockObject
     */
    private $productFactory;

    /**
     * @var PageFactory|MockObject
     */
    private $cmsFactory;

    /**
     * @var DateTime|MockObject
     */
    private $modelDate;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|MockObject
     */
    private $dateTime;

    /**
     * @var Category|MockObject
     */
    private $categoryProvider;

    /**
     * @var CmsPage|MockObject
     */
    private $cmsPageProvider;

    /**
     * @var StoreUrl|MockObject
     */
    private $storeUrlProvider;

    /**
     * @var ProductFactory|MockObject
     */
    private $batchProductFactory;

    /**
     * @var SitemapItemInterfaceFactory|MockObject
     */
    private $sitemapItemFactory;

    /**
     * @var ProductConfigReader|MockObject
     */
    private $productConfigReader;

    /**
     * @var Write|MockObject
     */
    private $directory;

    /**
     * @var AbstractResource|MockObject
     */
    private $resource;

    /**
     * @var Store|MockObject
     */
    private $store;

    /**
     * @return void
     * @throws Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->registry = $this->createMock(Registry::class);
        $this->escaper = $this->createMock(Escaper::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->categoryFactory = $this->createMock(CategoryFactory::class);
        $this->productFactory = $this->createMock(BaseProductFactory::class);
        $this->cmsFactory = $this->createMock(PageFactory::class);
        $this->modelDate = $this->createMock(DateTime::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->request = $this->createMock(RequestInterface::class);
        $this->dateTime = $this->createMock(\Magento\Framework\Stdlib\DateTime::class);
        $this->categoryProvider = $this->createMock(Category::class);
        $this->cmsPageProvider = $this->createMock(CmsPage::class);
        $this->storeUrlProvider = $this->createMock(StoreUrl::class);
        $this->batchProductFactory = $this->createMock(ProductFactory::class);
        $this->sitemapItemFactory = $this->createMock(SitemapItemInterfaceFactory::class);
        $this->productConfigReader = $this->createMock(ProductConfigReader::class);
        $this->directory = $this->createMock(Write::class);

        $this->sitemapData = $this->createPartialMockWithReflection(
            Data::class,
            ['getBaseUrl', 'getEnableSubmissionRobots', 'getMaximumLinesNumber', 'getMaximumFileSize']
        );

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')
            ->willReturn('http://example.com/');

        $this->store = $this->createPartialMock(Store::class, ['getId']);

        $this->store->method('getId')
            ->willReturn(1);

        $this->storeManager->method('getStore')
            ->willReturn($this->store);

        $this->sitemapData->method('getBaseUrl')
            ->willReturn('http://example.com/');

        $this->sitemapData->method('getEnableSubmissionRobots')
            ->willReturn(false);

        $this->sitemapData->method('getMaximumLinesNumber')
            ->willReturn(50000);

        $this->sitemapData->method('getMaximumFileSize')
            ->willReturn(10485760);

        $this->filesystem->method('getDirectoryWrite')
            ->willReturn($this->directory);

        $this->resource = $this->createMock(SitemapResource::class);

        $this->resource->method('getIdFieldName')
            ->willReturn('sitemap_id');

        $this->resource->method('beginTransaction')
            ->willReturnSelf();

        $this->resource->method('commit')
            ->willReturnSelf();

        $this->resource->method('save')
            ->willReturnSelf();

        $resourceCollection = $this->createMock(AbstractDb::class);
        $documentRoot = $this->createMock(DocumentRoot::class);
        $itemProvider = $this->createMock(ItemProviderInterface::class);
        $configReader = $this->createMock(SitemapConfigReaderInterface::class);

        $writeInterface = $this->createMock(WriteInterface::class);
        $writeInterface->method('write')
            ->willReturn(1);

        $this->directory->method('openFile')
            ->willReturn($writeInterface);

        $this->directory->method('isExist')
            ->willReturn(true);

        $this->sitemap = $this->getMockBuilder(Sitemap::class)
            ->setConstructorArgs([
                $this->context,
                $this->registry,
                $this->escaper,
                $this->sitemapData,
                $this->filesystem,
                $this->categoryFactory,
                $this->productFactory,
                $this->cmsFactory,
                $this->modelDate,
                $this->storeManager,
                $this->request,
                $this->dateTime,
                $this->resource,
                $resourceCollection,
                [],
                $documentRoot,
                $itemProvider,
                $configReader,
                $this->sitemapItemFactory,
                $this->categoryProvider,
                $this->cmsPageProvider,
                $this->storeUrlProvider,
                $this->batchProductFactory,
                $this->productConfigReader
            ])
            ->onlyMethods([
                'save',
                '_getUrl',
                '_getCurrentSitemapFilename',
                '_finalizeSitemap',
                '_createSitemapIndex',
                '_createSitemap',
                '_writeSitemapRow',
                '_isSplitRequired'
            ])
            ->getMock();

        $this->sitemap->method('save')->willReturnSelf();

        $this->sitemap->method('_getUrl')->willReturn('http://example.com/test-url');
        $this->sitemap->method('_getCurrentSitemapFilename')->willReturn('sitemap1.xml');
        $this->sitemap->method('_finalizeSitemap')->willReturnSelf();
        $this->sitemap->method('_createSitemapIndex')->willReturnSelf();
        $this->sitemap->method('_createSitemap')->willReturnSelf();
        $this->sitemap->method('_writeSitemapRow')->willReturnSelf();
        $this->sitemap->method('_isSplitRequired')->willReturn(false);

        $this->sitemap->setSitemapFilename('sitemap.xml');
        $this->sitemap->setSitemapPath('/var/www/html/');
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(Sitemap::class, $this->sitemap);
    }

    public function testInitSitemapItems()
    {
        $storeId = 1;
        $categoryItems = [$this->createMock(SitemapItemInterface::class)];
        $cmsPageItems = [$this->createMock(SitemapItemInterface::class)];
        $storeUrlItems = [$this->createMock(SitemapItemInterface::class)];

        $this->sitemap->setStoreId($storeId);

        $this->categoryProvider->expects($this->once())
            ->method('getItems')
            ->with($storeId)
            ->willReturn($categoryItems);

        $this->cmsPageProvider->expects($this->once())
            ->method('getItems')
            ->with($storeId)
            ->willReturn($cmsPageItems);

        $this->storeUrlProvider->expects($this->once())
            ->method('getItems')
            ->with($storeId)
            ->willReturn($storeUrlItems);

        $reflection = new \ReflectionClass($this->sitemap);
        $method = $reflection->getMethod('_initSitemapItems');
        $method->setAccessible(true);
        $method->invoke($this->sitemap);

        $sitemapItemsProperty = $reflection->getProperty('_sitemapItems');
        $sitemapItemsProperty->setAccessible(true);
        $sitemapItems = $sitemapItemsProperty->getValue($this->sitemap);

        $this->assertCount(3, $sitemapItems);
    }

    /**
     * @return void
     * @throws Exception
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGenerateXml()
    {
        $storeId = 1;
        $this->sitemap->setStoreId($storeId);

        // Mock category items
        $categoryItem = $this->createMock(SitemapItemInterface::class);
        $categoryItem->method('getUrl')->willReturn('/category/test');
        $categoryItem->method('getUpdatedAt')->willReturn('2023-01-01');
        $categoryItem->method('getChangeFrequency')->willReturn('weekly');
        $categoryItem->method('getPriority')->willReturn('0.5');
        $categoryItem->method('getImages')->willReturn([]);

        $this->categoryProvider->expects($this->once())
            ->method('getItems')
            ->with($storeId)
            ->willReturn([$categoryItem]);

        $this->cmsPageProvider->expects($this->once())
            ->method('getItems')
            ->with($storeId)
            ->willReturn([]);

        $this->storeUrlProvider->expects($this->once())
            ->method('getItems')
            ->with($storeId)
            ->willReturn([]);

        $batchProductResource = $this->createMock(Product::class);

        $product = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getUrl', 'getUpdatedAt', 'getImages']
        );

        $product->expects($this->any())
            ->method('getUrl')
            ->willReturn('/product/test');

        $product->expects($this->any())
            ->method('getUpdatedAt')
            ->willReturn('2023-01-01');

        $product->expects($this->any())
            ->method('getImages')
            ->willReturn([]);

        $this->batchProductFactory->expects($this->once())
            ->method('create')
            ->willReturn($batchProductResource);

        $batchProductResource->expects($this->once())
            ->method('getCollection')
            ->with($storeId)
            ->willReturn([$product]);

        $this->productConfigReader->expects($this->any())
            ->method('getPriority')
            ->with($storeId)
            ->willReturn('0.5');

        $this->productConfigReader->expects($this->any())
            ->method('getChangeFrequency')
            ->with($storeId)
            ->willReturn('daily');

        $sitemapItem = $this->createMock(SitemapItemInterface::class);
        $sitemapItem->method('getUrl')->willReturn('/product/test');
        $sitemapItem->method('getUpdatedAt')->willReturn('2023-01-01');
        $sitemapItem->method('getChangeFrequency')->willReturn('daily');
        $sitemapItem->method('getPriority')->willReturn('0.5');
        $sitemapItem->method('getImages')->willReturn([]);

        $this->sitemapItemFactory->expects($this->once())
            ->method('create')
            ->willReturn($sitemapItem);

        $this->modelDate->expects($this->once())
            ->method('gmtDate')
            ->with('Y-m-d H:i:s')
            ->willReturn('2023-01-01 00:00:00');

        $reflection = new \ReflectionClass($this->sitemap);

        $tagsProperty = $reflection->getProperty('_tags');
        $tagsProperty->setAccessible(true);
        $tagsProperty->setValue($this->sitemap, [
            'url' => [
                'open' => '<?xml version="1.0" encoding="UTF-8"?><urlset>',
                'close' => '</urlset>'
            ],
            'sitemap' => [
                'open' => '<?xml version="1.0" encoding="UTF-8"?><sitemapindex>',
                'close' => '</sitemapindex>'
            ]
        ]);

        $sitemapItemsProperty = $reflection->getProperty('_sitemapItems');
        $sitemapItemsProperty->setAccessible(true);
        $sitemapItemsProperty->setValue($this->sitemap, [$categoryItem]);

        // Initialize necessary properties for XML generation
        $fileProperty = $reflection->getProperty('_fileSize');
        $fileProperty->setAccessible(true);
        $fileProperty->setValue($this->sitemap, 0);

        $lineCountProperty = $reflection->getProperty('_lineCount');
        $lineCountProperty->setAccessible(true);
        $lineCountProperty->setValue($this->sitemap, 0);

        $incrementProperty = $reflection->getProperty('_sitemapIncrement');
        $incrementProperty->setAccessible(true);
        $incrementProperty->setValue($this->sitemap, 0);

        $result = $this->sitemap->generateXml();
        $this->assertInstanceOf(Sitemap::class, $result);
    }

    public function testStreamProducts()
    {
        $storeId = 1;
        $this->sitemap->setStoreId($storeId);

        $batchProductResource = $this->createMock(Product::class);

        $product1 = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getUrl', 'getUpdatedAt', 'getImages']
        );

        $product1->expects($this->any())
            ->method('getUrl')
            ->willReturn('/product/1');

        $product1->expects($this->any())
            ->method('getUpdatedAt')
            ->willReturn('2023-01-01');

        $product1->expects($this->any())
            ->method('getImages')
            ->willReturn([]);

        $product2 = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getUrl', 'getUpdatedAt', 'getImages']
        );

        $product2->expects($this->any())
            ->method('getUrl')
            ->willReturn('/product/2');

        $product2->expects($this->any())
            ->method('getUpdatedAt')
            ->willReturn('2023-01-02');

        $product2->expects($this->any())
            ->method('getImages')
            ->willReturn([]);

        $this->batchProductFactory->expects($this->once())
            ->method('create')
            ->willReturn($batchProductResource);

        $batchProductResource->expects($this->once())
            ->method('getCollection')
            ->with($storeId)
            ->willReturn([$product1, $product2]);

        $this->productConfigReader->expects($this->any())
            ->method('getPriority')
            ->with($storeId)
            ->willReturn('0.5');

        $this->productConfigReader->expects($this->any())
            ->method('getChangeFrequency')
            ->with($storeId)
            ->willReturn('daily');

        $sitemapItem = $this->createMock(SitemapItemInterface::class);
        $sitemapItem->method('getUrl')->willReturn('/product/test');
        $sitemapItem->method('getUpdatedAt')->willReturn('2023-01-01');
        $sitemapItem->method('getChangeFrequency')->willReturn('daily');
        $sitemapItem->method('getPriority')->willReturn('0.5');
        $sitemapItem->method('getImages')->willReturn([]);

        $this->sitemapItemFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturn($sitemapItem);

        $writeInterface = $this->createMock(WriteInterface::class);
        $writeInterface->expects($this->any())
            ->method('write')
            ->willReturn(1);

        $this->directory->expects($this->any())
            ->method('openFile')
            ->willReturn($writeInterface);

        $reflection = new \ReflectionClass($this->sitemap);

        $tagsProperty = $reflection->getProperty('_tags');
        $tagsProperty->setAccessible(true);
        $tagsProperty->setValue($this->sitemap, [
            'url' => [
                'open' => '<?xml version="1.0" encoding="UTF-8"?><urlset>',
                'close' => '</urlset>'
            ]
        ]);

        $fileProperty = $reflection->getProperty('_fileSize');
        $fileProperty->setAccessible(true);
        $fileProperty->setValue($this->sitemap, 100);

        $method = $reflection->getMethod('streamProducts');
        $method->setAccessible(true);
        $method->invoke($this->sitemap);
    }

    public function testStreamProductsWithEmptyCollection()
    {
        $storeId = 1;
        $this->sitemap->setStoreId($storeId);

        $batchProductResource = $this->createMock(Product::class);

        $this->batchProductFactory->expects($this->once())
            ->method('create')
            ->willReturn($batchProductResource);

        $batchProductResource->expects($this->once())
            ->method('getCollection')
            ->with($storeId)
            ->willReturn(false);

        $this->sitemapItemFactory->expects($this->never())
            ->method('create');

        $reflection = new \ReflectionClass($this->sitemap);
        $method = $reflection->getMethod('streamProducts');
        $method->setAccessible(true);
        $method->invoke($this->sitemap);
    }

    public function testProcessSitemapItem()
    {
        $sitemapItem = $this->createMock(SitemapItemInterface::class);
        $sitemapItem->method('getUrl')->willReturn('/test-url');
        $sitemapItem->method('getUpdatedAt')->willReturn('2023-01-01');
        $sitemapItem->method('getChangeFrequency')->willReturn('weekly');
        $sitemapItem->method('getPriority')->willReturn('0.5');
        $sitemapItem->method('getImages')->willReturn([]);

        $reflection = new \ReflectionClass($this->sitemap);

        $fileProperty = $reflection->getProperty('_fileSize');
        $fileProperty->setAccessible(true);
        $fileProperty->setValue($this->sitemap, 100);

        $lineCountProperty = $reflection->getProperty('_lineCount');
        $lineCountProperty->setAccessible(true);
        $lineCountProperty->setValue($this->sitemap, 0);

        $method = $reflection->getMethod('processSitemapItem');
        $method->setAccessible(true);

        $this->expectNotToPerformAssertions();
        $method->invoke($this->sitemap, $sitemapItem);
    }

    public function testProcessSitemapItemWithSplitRequired()
    {
        $sitemap = $this->getMockBuilder(Sitemap::class)
            ->setConstructorArgs([
                $this->context,
                $this->registry,
                $this->escaper,
                $this->sitemapData,
                $this->filesystem,
                $this->categoryFactory,
                $this->productFactory,
                $this->cmsFactory,
                $this->modelDate,
                $this->storeManager,
                $this->request,
                $this->dateTime,
                $this->resource,
                $this->createMock(AbstractDb::class),
                [],
                $this->createMock(DocumentRoot::class),
                $this->createMock(ItemProviderInterface::class),
                $this->createMock(SitemapConfigReaderInterface::class),
                $this->sitemapItemFactory,
                $this->categoryProvider,
                $this->cmsPageProvider,
                $this->storeUrlProvider,
                $this->batchProductFactory,
                $this->productConfigReader
            ])
            ->onlyMethods([
                'save',
                '_getUrl',
                '_getCurrentSitemapFilename',
                '_finalizeSitemap',
                '_createSitemap',
                '_writeSitemapRow',
                '_isSplitRequired',
                '_createSitemapIndex'
            ])
            ->getMock();

        $sitemap->method('_isSplitRequired')->willReturn(true);
        $sitemap->method('_finalizeSitemap')->willReturnSelf();
        $sitemap->method('_createSitemap')->willReturnSelf();
        $sitemap->method('_writeSitemapRow')->willReturnSelf();
        $sitemap->method('_getUrl')->willReturn('http://example.com/test-url');
        $sitemap->method('_getCurrentSitemapFilename')->willReturn('sitemap1.xml');
        $sitemap->method('_createSitemapIndex')->willReturnSelf();

        $sitemap->setSitemapFilename('sitemap.xml');
        $sitemap->setSitemapPath('/var/www/html/');

        $sitemapItem = $this->createMock(SitemapItemInterface::class);
        $sitemapItem->method('getUrl')->willReturn('/test-url');
        $sitemapItem->method('getUpdatedAt')->willReturn('2023-01-01');
        $sitemapItem->method('getChangeFrequency')->willReturn('weekly');
        $sitemapItem->method('getPriority')->willReturn('0.5');
        $sitemapItem->method('getImages')->willReturn([]);

        $reflection = new \ReflectionClass($sitemap);
        $sitemapIncrementProperty = $reflection->getProperty('_sitemapIncrement');
        $sitemapIncrementProperty->setAccessible(true);
        $sitemapIncrementProperty->setValue($sitemap, 1);

        $fileSizeProperty = $reflection->getProperty('_fileSize');
        $fileSizeProperty->setAccessible(true);
        $fileSizeProperty->setValue($sitemap, 10000000); // Large file size to trigger split

        $lineCountProperty = $reflection->getProperty('_lineCount');
        $lineCountProperty->setAccessible(true);
        $lineCountProperty->setValue($sitemap, 1);

        $method = $reflection->getMethod('processSitemapItem');
        $method->setAccessible(true);

        $this->expectNotToPerformAssertions();
        $method->invoke($sitemap, $sitemapItem);
    }
}

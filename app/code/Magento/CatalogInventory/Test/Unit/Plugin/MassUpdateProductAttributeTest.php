<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute as AttributeHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor as StockIndexerProcessor;
use Magento\CatalogInventory\Model\Stock\Item as StockItem;
use Magento\CatalogInventory\Observer\ParentItemProcessorInterface;
use Magento\CatalogInventory\Plugin\MassUpdateProductAttribute;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassUpdateProductAttributeTest extends TestCase
{
    private const WEBSITE_ID = 1;

    private const MISSING_PRODUCT_MESSAGE = 'The product with ID "20" does not exist.';

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepository;

    /**
     * @var ProductCollectionFactory|MockObject
     */
    private $productCollectionFactory;

    /**
     * @var StockItemRepositoryInterface|MockObject
     */
    private $stockItemRepository;

    /**
     * @var StockIndexerProcessor|MockObject
     */
    private $stockIndexerProcessor;

    /**
     * @var ParentItemProcessorInterface|MockObject
     */
    private $parentItemProcessor;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManager;

    /**
     * @var AttributeHelper
     */
    private AttributeHelper $attributeHelper;

    /**
     * @var ResultInterface
     */
    private ResultInterface $result;

    /**
     * @var MassUpdateProductAttribute
     */
    private MassUpdateProductAttribute $plugin;

    /**
     * @var Product[]
     */
    private array $processedProducts = [];

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->productCollectionFactory = $this->createMock(ProductCollectionFactory::class);
        $this->stockItemRepository = $this->createMock(StockItemRepositoryInterface::class);
        $this->stockIndexerProcessor = $this->createMock(StockIndexerProcessor::class);
        $this->parentItemProcessor = $this->createMock(ParentItemProcessorInterface::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->result = $this->createStub(ResultInterface::class);
        $this->attributeHelper = $this->createStub(AttributeHelper::class);
        $this->attributeHelper->method('getSelectedStoreId')->willReturn(0);
        $this->attributeHelper->method('getStoreWebsiteId')->willReturn(self::WEBSITE_ID);

        $stockConfiguration = $this->createStub(StockConfigurationInterface::class);
        $stockConfiguration->method('getConfigItemOptions')->willReturn([]);

        $stockRegistry = $this->createStub(StockRegistryInterface::class);
        $stockRegistry->method('getStockItem')->willReturnCallback(
            function ($productId): StockItemInterface {
                $stockItem = $this->createStub(StockItem::class);
                $stockItem->method('getProductId')->willReturn((int)$productId);
                $stockItem->method('getId')->willReturn((int)$productId);
                return $stockItem;
            }
        );

        $this->plugin = new MassUpdateProductAttribute(
            $this->stockIndexerProcessor,
            $this->createStub(DataObjectHelper::class),
            $stockRegistry,
            $this->stockItemRepository,
            $stockConfiguration,
            $this->attributeHelper,
            $this->messageManager,
            $this->productRepository,
            [$this->parentItemProcessor],
            $this->productCollectionFactory
        );
    }

    public function testSelectedProductsAreLoadedWithOneCollectionInsteadOfOneRepositoryLoadEach(): void
    {
        $productIds = ['10', '20', '30'];
        $products = $this->createProducts($productIds);
        $this->attributeHelper->method('getProductIds')->willReturn($productIds);

        $this->productCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->createProductCollection($productIds, $products));
        $this->productRepository->expects($this->never())->method('getById');
        $this->stockItemRepository->expects($this->exactly(3))->method('save');
        $this->stockIndexerProcessor->expects($this->once())->method('reindexList')->with($productIds);
        $this->expectProcessedProducts(3);
        $this->messageManager->expects($this->never())->method('addExceptionMessage');
        $this->messageManager->expects($this->never())->method('addErrorMessage');

        $this->assertSame(
            $this->result,
            $this->plugin->aroundExecute($this->createSaveController(), fn () => $this->result)
        );
        $this->assertSame(array_values($products), $this->processedProducts);
    }

    public function testLargeSelectionIsLoadedInBatchesInSelectionOrder(): void
    {
        $productIds = array_map('strval', range(1001, 1501));
        $products = $this->createProducts($productIds);
        $this->attributeHelper->method('getProductIds')->willReturn($productIds);

        $this->productCollectionFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls(
                $this->createProductCollection(array_slice($productIds, 0, 500), $products),
                $this->createProductCollection(array_slice($productIds, 500), $products)
            );
        $this->productRepository->expects($this->never())->method('getById');
        $this->stockItemRepository->expects($this->exactly(501))->method('save');
        $this->stockIndexerProcessor->expects($this->once())->method('reindexList')->with($productIds);
        $this->expectProcessedProducts(501);
        $this->messageManager->expects($this->never())->method('addExceptionMessage');

        $this->plugin->aroundExecute($this->createSaveController(), fn () => $this->result);

        $this->assertSame(array_values($products), $this->processedProducts);
    }

    public function testProductMissingFromCollectionFallsBackToRepository(): void
    {
        $productIds = ['10', '20'];
        $products = $this->createProducts(['10']);
        $this->attributeHelper->method('getProductIds')->willReturn($productIds);

        $this->productCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->createProductCollection($productIds, $products));
        $this->productRepository->expects($this->once())
            ->method('getById')
            ->with('20')
            ->willThrowException(new NoSuchEntityException(__('The product with ID "%1" does not exist.', '20')));
        $this->stockItemRepository->expects($this->once())->method('save');
        $this->stockIndexerProcessor->expects($this->never())->method('reindexList');
        $this->parentItemProcessor->expects($this->once())->method('process')->with($products['10']);
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with(self::MISSING_PRODUCT_MESSAGE);

        $this->assertSame(
            $this->result,
            $this->plugin->aroundExecute($this->createSaveController(), fn () => $this->result)
        );
    }

    /**
     * @param string[] $productIds
     * @return Product[]
     */
    private function createProducts(array $productIds): array
    {
        $products = [];
        foreach ($productIds as $productId) {
            $products[$productId] = $this->createConfiguredStub(Product::class, ['getId' => $productId]);
        }

        return $products;
    }

    /**
     * @param string[] $productIds
     * @param Product[] $products
     */
    private function createProductCollection(array $productIds, array $products): ProductCollection
    {
        $collection = $this->createMock(ProductCollection::class);
        $collection->expects($this->once())
            ->method('addIdFilter')
            ->with($productIds)
            ->willReturnSelf();
        $collection->expects($this->exactly(count($productIds)))
            ->method('getItemById')
            ->willReturnCallback(fn ($productId) => $products[$productId] ?? null);

        return $collection;
    }

    private function expectProcessedProducts(int $count): void
    {
        $this->parentItemProcessor->expects($this->exactly($count))
            ->method('process')
            ->willReturnCallback(
                function (Product $product): void {
                    $this->processedProducts[] = $product;
                }
            );
    }

    private function createSaveController(): Save
    {
        $request = $this->createStub(RequestInterface::class);
        $request->method('getParam')->willReturnMap([['inventory', [], ['qty' => 5]]]);

        return $this->createConfiguredStub(Save::class, ['getRequest' => $request]);
    }
}

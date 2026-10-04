<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Indexer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\ResourceModel\Rule as RuleResource;
use Magento\CatalogRule\Test\Fixture\Rule as CatalogRuleFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for dynamic batch sizing optimizations
 */
#[
    AppArea('crontab'),
    DbIsolation(false)
]
class DynamicBatchSizingTest extends TestCase
{
    /**
     * @var IndexBuilder
     */
    private $indexBuilder;

    /**
     * @var RuleResource
     */
    private $ruleResource;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var array
     */
    private $createdProductIds = [];

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->indexBuilder = $objectManager->get(IndexBuilder::class);
        $this->ruleResource = $objectManager->get(RuleResource::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        foreach ($this->createdProductIds as $productId) {
            try {
                $this->productRepository->deleteById($productId);
            } catch (\Exception $e) {
            }
        }
        $this->createdProductIds = [];
        parent::tearDown();
    }

    /**
     * Create test products for memory efficiency testing
     *
     * @param int $count Number of products to create
     * @return array Product IDs
     */
    private function createTestProducts(int $count): array
    {
        $objectManager = Bootstrap::getObjectManager();
        $productFactory = $objectManager->get(ProductFactory::class);

        $productIds = [];
        for ($i = 1; $i <= $count; $i++) {
            $product = $productFactory->create();
            $product->setTypeId('simple')
                ->setAttributeSetId(4)
                ->setWebsiteIds([1])
                ->setName("Test Product {$i}")
                ->setSku("perf-test-{$i}")
                ->setPrice(100 + $i)
                ->setVisibility(4)
                ->setStatus(1);
            $product = $this->productRepository->save($product);
            $productIds[] = $product->getId();
        }

        $this->createdProductIds = array_merge($this->createdProductIds, $productIds);
        return $productIds;
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 100.00, 'sku' => 'simple-prod-1'], 'product1'),
        DataFixture(ProductFixture::class, ['price' => 200.00, 'sku' => 'simple-prod-2'], 'product2'),
        DataFixture(ProductFixture::class, ['price' => 300.00, 'sku' => 'simple-prod-3'], 'product3'),
        DataFixture(
            CatalogRuleFixture::class,
            [
                'discount_amount' => 10,
                'simple_action' => 'by_percent'
            ],
            'rule'
        )
    ]
    public function testFullReindexWithDynamicBatchSizing(): void
    {
        $product1 = $this->fixtures->get('product1');
        $product2 = $this->fixtures->get('product2');
        $product3 = $this->fixtures->get('product3');

        $this->indexBuilder->reindexFull();

        $rulePrice1 = $this->ruleResource->getRulePrice(new \DateTime(), 1, 1, $product1->getId());
        $rulePrice2 = $this->ruleResource->getRulePrice(new \DateTime(), 1, 1, $product2->getId());
        $rulePrice3 = $this->ruleResource->getRulePrice(new \DateTime(), 1, 1, $product3->getId());

        $this->assertEquals(90.00, $rulePrice1, 'Product 1 should have 10% discount');
        $this->assertEquals(180.00, $rulePrice2, 'Product 2 should have 10% discount');
        $this->assertEquals(270.00, $rulePrice3, 'Product 3 should have 10% discount');
    }

    #[
        DataFixture(
            CatalogRuleFixture::class,
            [
                'discount_amount' => 15,
                'simple_action' => 'by_percent'
            ],
            'rule'
        )
    ]
    public function testMemoryEfficiencyWithMultipleProducts(): void
    {
        $productIds = $this->createTestProducts(20);

        $memoryBefore = memory_get_usage(true);

        $this->indexBuilder->reindexFull();

        $memoryAfter = memory_get_usage(true);
        $memoryUsed = $memoryAfter - $memoryBefore;

        foreach ($productIds as $productId) {
            $rulePrice = $this->ruleResource->getRulePrice(new \DateTime(), 1, 1, $productId);
            $this->assertNotFalse($rulePrice, "Product {$productId} should have a rule price");
        }

        $this->assertLessThan(
            50 * 1024 * 1024,
            $memoryUsed,
            sprintf(
                'Memory usage (%.2f MB) exceeded threshold during indexing of 20 products',
                $memoryUsed / (1024 * 1024)
            )
        );
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 150.00, 'sku' => 'multi-group-prod'], 'product'),
        DataFixture(
            CatalogRuleFixture::class,
            [
                'discount_amount' => 25,
                'simple_action' => 'by_percent',
                'customer_group_ids' => [0, 1, 2, 3] // Multiple customer groups
            ],
            'rule'
        )
    ]
    public function testReindexWithMultipleCustomerGroups(): void
    {
        $product = $this->fixtures->get('product');

        $this->indexBuilder->reindexFull();

        foreach ([0, 1, 2, 3] as $customerGroupId) {
            $rulePrice = $this->ruleResource->getRulePrice(
                new \DateTime(),
                1,
                $customerGroupId,
                $product->getId()
            );

            $this->assertEquals(
                112.50, // 25% off 150
                $rulePrice,
                "Product should have 25% discount for customer group {$customerGroupId}"
            );
        }
    }
}

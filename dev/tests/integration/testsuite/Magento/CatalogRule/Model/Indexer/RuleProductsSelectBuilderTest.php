<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\ResourceModel\Rule as RuleResource;
use Magento\Customer\Model\GroupManagement;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Covers catalog rule price indexing for products without a price value on the default (store_id = 0) scope.
 */
class RuleProductsSelectBuilderTest extends TestCase
{
    private const SKU = 'simple';

    /**
     * @var RuleProductsSelectBuilder
     */
    private $selectBuilder;

    /**
     * @var IndexBuilder
     */
    private $indexBuilder;

    /**
     * @var RuleResource
     */
    private $ruleResource;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->selectBuilder = $objectManager->get(RuleProductsSelectBuilder::class);
        $this->indexBuilder = $objectManager->get(IndexBuilder::class);
        $this->ruleResource = $objectManager->get(RuleResource::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->storeManager = $objectManager->get(StoreManagerInterface::class);
        $this->resource = $objectManager->get(ResourceConnection::class);
    }

    /**
     * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/product_simple.php
     * @magentoDataFixtureBeforeTransaction Magento/CatalogRule/_files/catalog_rule_10_off_not_logged.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testProductWithoutDefaultScopePriceIsSelected(): void
    {
        $product = $this->productRepository->get(self::SKU);
        $websiteId = (int)$this->storeManager->getWebsite('base')->getId();
        $this->movePriceToStore($product, (int)$this->storeManager->getStore('default')->getId(), 100.0);

        $rows = $this->selectBuilder->buildSelect($websiteId, [(int)$product->getId()])->fetchAll();

        $this->assertNotEmpty($rows, 'Product matched by the rule is missing from the rule price select.');
        $this->assertEquals(100.0, (float)$rows[0]['default_price']);
    }

    /**
     * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/product_simple.php
     * @magentoDataFixtureBeforeTransaction Magento/CatalogRule/_files/catalog_rule_10_off_not_logged.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testRulePriceIsIndexedForProductWithoutDefaultScopePrice(): void
    {
        $product = $this->productRepository->get(self::SKU);
        $productId = (int)$product->getId();
        $websiteId = (int)$this->storeManager->getWebsite('base')->getId();
        $this->movePriceToStore($product, (int)$this->storeManager->getStore('default')->getId(), 100.0);

        $this->indexBuilder->reindexById($productId);

        $rulePrice = $this->ruleResource->getRulePrice(
            new \DateTime(),
            $websiteId,
            GroupManagement::NOT_LOGGED_IN_ID,
            $productId
        );
        $this->assertEquals(90.0, (float)$rulePrice);
    }

    /**
     * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/product_simple.php
     * @magentoDataFixtureBeforeTransaction Magento/CatalogRule/_files/catalog_rule_10_off_not_logged.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testDefaultScopePriceIsUsedWhenStorePriceIsMissing(): void
    {
        $product = $this->productRepository->get(self::SKU);
        $websiteId = (int)$this->storeManager->getWebsite('base')->getId();

        $rows = $this->selectBuilder->buildSelect($websiteId, [(int)$product->getId()])->fetchAll();

        $this->assertNotEmpty($rows);
        $this->assertEquals(10.0, (float)$rows[0]['default_price']);
    }

    /**
     * Replace the default scope price with a store view scope price, as with website-scoped price management.
     *
     * @param ProductInterface $product
     * @param int $storeId
     * @param float $price
     * @return void
     */
    private function movePriceToStore(ProductInterface $product, int $storeId, float $price): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $priceAttribute = $objectManager->get(EavConfig::class)->getAttribute(Product::ENTITY, 'price');
        $linkField = $objectManager->get(MetadataPool::class)->getMetadata(ProductInterface::class)->getLinkField();
        $connection = $this->resource->getConnection();
        $table = $priceAttribute->getBackend()->getTable();
        $linkId = (int)$product->getData($linkField);

        $connection->delete(
            $table,
            [
                'attribute_id = ?' => (int)$priceAttribute->getId(),
                $linkField . ' = ?' => $linkId,
                'store_id = ?' => Store::DEFAULT_STORE_ID,
            ]
        );
        $connection->insertOnDuplicate(
            $table,
            [
                'attribute_id' => (int)$priceAttribute->getId(),
                $linkField => $linkId,
                'store_id' => $storeId,
                'value' => $price,
            ],
            ['value']
        );
    }
}

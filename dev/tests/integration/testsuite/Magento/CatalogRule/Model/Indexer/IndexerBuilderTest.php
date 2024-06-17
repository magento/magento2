<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Indexer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;

#[
    DbIsolation(false),
    AppIsolation(true),
]
class IndexerBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\IndexBuilder
     */
    protected $indexerBuilder;

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Rule
     */
    protected $resourceRule;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productSecond;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productThird;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @var Processor
     */
    private $indexProductProcessor;

    protected function setUp(): void
    {
        $this->indexerBuilder = Bootstrap::getObjectManager()->get(
            \Magento\CatalogRule\Model\Indexer\IndexBuilder::class
        );
        $this->resourceRule = Bootstrap::getObjectManager()->get(\Magento\CatalogRule\Model\ResourceModel\Rule::class);
        $this->product = Bootstrap::getObjectManager()->get(\Magento\Catalog\Model\Product::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->connection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $this->indexProductProcessor = Bootstrap::getObjectManager()->get(Processor::class);
    }

    protected function tearDown(): void
    {
        /** @var \Magento\Framework\Registry $registry */
        $registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Registry::class);

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class
        );
        $productCollection->delete();

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/CatalogRule/_files/attribute.php
     * @magentoDataFixture Magento/CatalogRule/_files/rule_by_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testReindexById()
    {
        $product = $this->product->loadByAttribute('sku', 'simple');
        $product->load($product->getId());
        $product->setData('test_attribute', 'test_attribute_value')->save();

        $this->indexerBuilder->reindexById($product->getId());

        $this->assertEquals(9.8, $this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $product->getId()));
    }

    /**
     * @magentoDataFixture Magento/CatalogRule/_files/simple_product_with_catalog_rule_50_percent_off_tomorrow.php
     * @magentoConfigFixture base_website general/locale/timezone Europe/Amsterdam
     * @magentoConfigFixture general/locale/timezone America/Chicago
     */
    public function testReindexByIdDifferentTimezones()
    {
        $productId = $this->productRepository->get('simple')->getId();
        $this->indexerBuilder->reindexById($productId);

        $mainWebsiteId = $this->storeManager->getWebsite('base')->getId();
        $secondWebsiteId = $this->storeManager->getWebsite('test')->getId();
        $rawTimestamp = (new \DateTime('+1 day'))->getTimestamp();
        $timestamp = $rawTimestamp - ($rawTimestamp % (60 * 60 * 24));
        $mainWebsiteActiveRules =
            $this->resourceRule->getRulesFromProduct($timestamp, $mainWebsiteId, 1, $productId);
        $secondWebsiteActiveRules =
            $this->resourceRule->getRulesFromProduct($timestamp, $secondWebsiteId, 1, $productId);

        $this->assertCount(1, $mainWebsiteActiveRules);
        // Avoid failure when staging is enabled as it removes catalog rule timestamp.
        if ((int)$mainWebsiteActiveRules[0]['from_time'] !== 0) {
            $this->assertCount(0, $secondWebsiteActiveRules);
        }
    }

    /**
     * @magentoDataFixture Magento/CatalogRule/_files/attribute.php
     * @magentoDataFixture Magento/CatalogRule/_files/rule_by_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testReindexByIds()
    {
        $this->prepareProducts();

        $this->indexerBuilder->reindexByIds(
            [
                $this->product->getId(),
                $this->productSecond->getId(),
                $this->productThird->getId(),
            ]
        );

        $this->assertEquals(9.8, $this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $this->product->getId()));
        $this->assertEquals(
            9.8,
            $this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $this->productSecond->getId())
        );
        $this->assertFalse($this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $this->productThird->getId()));
    }

    /**
     * @magentoDataFixtureBeforeTransaction Magento/CatalogRule/_files/attribute.php
     * @magentoDataFixtureBeforeTransaction Magento/CatalogRule/_files/rule_by_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testReindexFull()
    {
        $this->prepareProducts();

        $this->indexerBuilder->reindexFull();

        $rulePrice = $this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $this->product->getId());
        $this->assertEquals(9.8, $rulePrice);
        $rulePrice = $this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $this->productSecond->getId());
        $this->assertEquals(9.8, $rulePrice);
        $this->assertFalse($this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $this->productThird->getId()));
    }

    /**
     * Tests restoring triggers on `catalogrule_product_price` table after full reindexing in 'Update by schedule' mode.
     */
    public function testRestoringTriggersAfterFullReindex()
    {
        $tableName = $this->connection->getTableName('catalogrule_product_price');

        $this->indexProductProcessor->getIndexer()->setScheduled(false);
        $this->assertEquals(0, $this->getTriggersCount($tableName));

        $this->indexProductProcessor->getIndexer()->setScheduled(true);
        $this->assertGreaterThan(0, $this->getTriggersCount($tableName));

        $this->indexerBuilder->reindexFull();
        $this->assertGreaterThan(0, $this->getTriggersCount($tableName));

        $this->indexProductProcessor->getIndexer()->setScheduled(false);
        $this->assertEquals(0, $this->getTriggersCount($tableName));
    }

    #[
        DataFixture('Magento/CatalogRule/_files/simple_product_with_catalog_rule_50_percent_off.php'),
    ]
    public function testReindexByIdForSecondStore(): void
    {
        $websiteId = $this->storeManager->getWebsite('test')->getId();
        $simpleProduct = $this->productRepository->get('simple');
        $this->indexerBuilder->reindexById($simpleProduct->getId());
        $rulePrice = $this->resourceRule->getRulePrice(new \DateTime(), $websiteId, 1, $simpleProduct->getId());
        $this->assertEquals(25, $rulePrice);
    }

    #[
        DataFixture('Magento/CatalogRule/_files/simple_product_with_catalog_rule_50_percent_off.php'),
    ]
    public function testReindexByIdsForSecondStore(): void
    {
        $websiteId = $this->storeManager->getWebsite('test')->getId();
        $simpleProduct = $this->productRepository->get('simple');
        $this->indexerBuilder->reindexByIds([$simpleProduct->getId()]);
        $rulePrice = $this->resourceRule->getRulePrice(new \DateTime(), $websiteId, 1, $simpleProduct->getId());
        $this->assertEquals(25, $rulePrice);
    }

    #[
        DataFixture('Magento/CatalogRule/_files/simple_product_with_catalog_rule_50_percent_off.php'),
    ]
    public function testReindexFullForSecondStore(): void
    {
        $websiteId = $this->storeManager->getWebsite('test')->getId();
        $simpleProduct = $this->productRepository->get('simple');
        $this->indexerBuilder->reindexFull();
        $rulePrice = $this->resourceRule->getRulePrice(new \DateTime(), $websiteId, 1, $simpleProduct->getId());
        $this->assertEquals(25, $rulePrice);
    }

    /**
     * Returns triggers count.
     *
     * @param string $tableName
     * @return int
     * @throws \Zend_Db_Statement_Exception
     */
    private function getTriggersCount(string $tableName): int
    {
        return count(
            $this->connection->getConnection()
                ->query('SHOW TRIGGERS LIKE \''. $tableName . '\'')
                ->fetchAll()
        );
    }

    protected function prepareProducts()
    {
        $product = $this->product->loadByAttribute('sku', 'simple');
        $product->load($product->getId());
        $this->product = $product;

        $this->product->setStoreId(0)->setData('test_attribute', 'test_attribute_value')->save();
        $this->productSecond = clone $this->product;
        $this->productSecond->setId(null)->setUrlKey('product-second')->save();
        $this->productThird = clone $this->product;
        $this->productThird->setId(null)
            ->setUrlKey('product-third')
            ->setData('test_attribute', 'NO_test_attribute_value')
            ->save();
    }
}

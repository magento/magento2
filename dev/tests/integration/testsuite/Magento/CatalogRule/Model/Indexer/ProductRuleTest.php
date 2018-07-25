<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Indexer;

use Magento\Catalog\Model\ProductRepository;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ProductRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Rule
     */
    protected $resourceRule;

    protected function setUp()
    {
        $this->resourceRule = Bootstrap::getObjectManager()->get(\Magento\CatalogRule\Model\ResourceModel\Rule::class);

        Bootstrap::getObjectManager()->get(\Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor::class)
            ->getIndexer()->isScheduled(false);
    }

    /**
     * @magentoDataFixture Magento/CatalogRule/_files/attribute.php
     * @magentoDataFixture Magento/CatalogRule/_files/rule_by_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppArea adminhtml
     */
    public function testReindexAfterSuitableProductSaving()
    {
        /** @var ProductRepository $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            ProductRepository::class
        );
        $product = $productRepository->get('simple');
        $product->setData('test_attribute', 'test_attribute_value')->save();

        $this->assertEquals(9.8, $this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $product->getId()));
    }

    /**
     * Checks whether category price rule applies to product with visibility value "Not Visibility Individually".
     *
     * @magentoDataFixture Magento/CatalogRule/_files/rule_by_category_ids.php
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     */
    public function testReindexWithProductNotVisibleIndividually()
    {
        /** @var ProductRepository $productRepository */
        $productRepository = Bootstrap::getObjectManager()->create(
            ProductRepository::class
        );
        $product = $productRepository->get('simple-3');

        $indexBuilder = Bootstrap::getObjectManager()->get(
            IndexBuilder::class
        );
        $indexBuilder->reindexById($product->getId());

        $this->assertEquals(
            7.5,
            $this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $product->getId()),
            "Catalog price rule doesn't apply to product with visibility value \"Not Visibility Individually\""
        );
    }
}

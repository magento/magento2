<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Indexer;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ProductRuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Rule
     */
    protected $resourceRule;

    protected function setUp()
    {
        $this->resourceRule = Bootstrap::getObjectManager()->get('Magento\CatalogRule\Model\ResourceModel\Rule');

        Bootstrap::getObjectManager()->get('Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor')
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
        /** @var \Magento\Catalog\Model\ProductRepository $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\ProductRepository'
        );
        $product = $productRepository->get('simple');
        $product->setData('test_attribute', 'test_attribute_value')->save();

        $this->assertEquals(9.8, $this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $product->getId()));
    }
}

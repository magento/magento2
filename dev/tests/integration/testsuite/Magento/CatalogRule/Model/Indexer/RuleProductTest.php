<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Indexer;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class RuleProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\IndexBuilder
     */
    protected $indexBuilder;

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Rule
     */
    protected $resourceRule;

    protected function setUp()
    {
        $this->indexBuilder = Bootstrap::getObjectManager()->get(
            \Magento\CatalogRule\Model\Indexer\IndexBuilder::class
        );
        $this->resourceRule = Bootstrap::getObjectManager()->get(\Magento\CatalogRule\Model\ResourceModel\Rule::class);
    }

    /**
     * @magentoDataFixture Magento/CatalogRule/_files/attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testReindexAfterRuleCreation()
    {
        /** @var \Magento\Catalog\Model\ProductRepository $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ProductRepository::class
        );
        $product = $productRepository->get('simple');
        $product->setData('test_attribute', 'test_attribute_value')->save();
        $this->assertFalse($this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $product->getId()));

        $this->saveRule();
        // apply all rules
        $this->indexBuilder->reindexFull();

        $this->assertEquals(9.8, $this->resourceRule->getRulePrice(new \DateTime(), 1, 1, $product->getId()));
    }

    protected function saveRule()
    {
        require 'Magento/CatalogRule/_files/rule_by_attribute.php';
    }
}

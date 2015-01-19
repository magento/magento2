<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var \Magento\CatalogRule\Model\Resource\Rule
     */
    protected $resourceRule;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    protected function setUp()
    {
        $this->indexBuilder = Bootstrap::getObjectManager()->get('Magento\CatalogRule\Model\Indexer\IndexBuilder');
        $this->resourceRule = Bootstrap::getObjectManager()->get('Magento\CatalogRule\Model\Resource\Rule');
        $this->product = Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
    }

    /**
     * @magentoDataFixture Magento/CatalogRule/_files/attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testReindexAfterRuleCreation()
    {
        $this->product->load(1)->setData('test_attribute', 'test_attribute_value')->save();
        $this->assertFalse($this->resourceRule->getRulePrice(true, 1, 1, 1));

        $this->saveRule();
        // apply all rules
        $this->indexBuilder->reindexFull();

        $this->assertEquals(9.8, $this->resourceRule->getRulePrice(true, 1, 1, 1));
    }

    protected function saveRule()
    {
        require 'Magento/CatalogRule/_files/rule_by_attribute.php';
    }
}

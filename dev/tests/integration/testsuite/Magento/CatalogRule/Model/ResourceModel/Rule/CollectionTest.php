<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\ResourceModel\Rule;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\CatalogRule\Model\ResourceModel\Rule\Collection as RuleCollection;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Setup\CategorySetup;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var IndexBuilder
     */
    protected $indexBuilder;

    /**
     * @var Rule
     */
    protected $resourceRule;

    /**
     * @var Collection
     */
    protected $resourceRuleCollection;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->indexBuilder = $this->objectManager->get(IndexBuilder::class);
        $this->resourceRule = $this->objectManager->get(Rule::class);
        $this->resourceRuleCollection = $this->objectManager->get(Collection::class);
    }

    /**
     * @magentoAppArea adminhtml
     *
     * @magentoDataFixture Magento/CatalogRule/_files/attribute.php
     * @magentoDataFixture Magento/CatalogRule/_files/rule_by_attribute.php
     * @magentoDataFixture Magento/CatalogRule/_files/two_rules.php
     */
    public function testReindexAfterRuleCreation()
    {
        $this->indexBuilder->reindexFull();
        $installer = $this->objectManager->create(CategorySetup::class);

        $resourceRuleCollection = $this->objectManager->create(RuleCollection::class);
        $resourceRuleCollection->addFilter('is_active', 1);
        $this->assertEquals(3, $resourceRuleCollection->count());

        $resourceRuleCollection = $this->objectManager->create(RuleCollection::class);
        $resourceRuleCollection->addFilter('is_active', 1);
        $resourceRuleCollection->addFilter('name', 'test_rule');
        $this->assertEquals(1, $resourceRuleCollection->count());

        $model = $this->objectManager->create(Attribute::class);
        $model->loadByCode($installer->getEntityTypeId('catalog_product'), 'test_attribute');
        $model->delete();

        $resourceRuleCollection = $this->objectManager->create(RuleCollection::class);
        $resourceRuleCollection->addFilter('is_active', 1);
        $this->assertEquals(2, $resourceRuleCollection->count());

        $resourceRuleCollection = $this->objectManager->create(RuleCollection::class);
        $resourceRuleCollection->addFilter('is_active', 1);
        $resourceRuleCollection->addFilter('name', 'test_rule');
        $this->assertEquals(0, $resourceRuleCollection->count());
    }
}

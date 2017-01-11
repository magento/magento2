<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogWidget\Model\Rule\Condition;


class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogWidget\Model\Rule\Condition\Product
     */
    protected $conditionProduct;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $rule = $this->objectManager->create(\Magento\CatalogWidget\Model\Rule::class);
        $this->conditionProduct = $this->objectManager->create(
            \Magento\CatalogWidget\Model\Rule\Condition\Product::class
        );
        $this->conditionProduct->setRule($rule);
    }

    public function testLoadAttributeOptions()
    {
        $this->conditionProduct->loadAttributeOptions();
        $options = $this->conditionProduct->getAttributeOption();
        $this->assertArrayHasKey('sku', $options);
        $this->assertArrayHasKey('attribute_set_id', $options);
        $this->assertArrayHasKey('category_ids', $options);
        foreach ($options as $code => $label) {
            $this->assertNotEmpty($label);
            $this->assertNotEmpty($code);
        }
    }

    public function testAddGlobalAttributeToCollection()
    {
        $collection = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $this->conditionProduct->setAttribute('special_price');
        $this->conditionProduct->addToCollection($collection);
        $collectedAttributes = $this->conditionProduct->getRule()->getCollectedAttributes();
        $this->assertArrayHasKey('special_price', $collectedAttributes);
        $query = (string)$collection->getSelect();
        $this->assertContains('special_price', $query);
        $this->assertEquals('at_special_price.value', $this->conditionProduct->getMappedSqlField());
    }

    public function testAddNonGlobalAttributeToCollectionNoProducts()
    {
        $collection = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $this->conditionProduct->setAttribute('visibility');
        $this->conditionProduct->setOperator('()');
        $this->conditionProduct->setValue('4');
        $this->conditionProduct->addToCollection($collection);
        $collectedAttributes = $this->conditionProduct->getRule()->getCollectedAttributes();
        $this->assertArrayHasKey('visibility', $collectedAttributes);
        $query = (string)$collection->getSelect();
        $this->assertNotContains('visibility', $query);
        $this->assertEquals('', $this->conditionProduct->getMappedSqlField());
        $this->assertFalse($this->conditionProduct->hasValueParsed());
        $this->assertFalse($this->conditionProduct->hasValue());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testAddNonGlobalAttributeToCollection()
    {
        $collection = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $this->conditionProduct->setAttribute('visibility');
        $this->conditionProduct->setOperator('()');
        $this->conditionProduct->setValue('4');
        $this->conditionProduct->addToCollection($collection);
        $collectedAttributes = $this->conditionProduct->getRule()->getCollectedAttributes();
        $this->assertArrayHasKey('visibility', $collectedAttributes);
        $query = (string)$collection->getSelect();
        $this->assertNotContains('visibility', $query);
        $this->assertEquals('e.entity_id', $this->conditionProduct->getMappedSqlField());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetMappedSqlFieldCategoryIdsAttribute()
    {
        $this->conditionProduct->setAttribute('category_ids');
        $this->assertEquals('e.entity_id', $this->conditionProduct->getMappedSqlField());
    }
}

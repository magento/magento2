<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogWidget\Model\Rule\Condition;


class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogWidget\Model\Rule\Condition\Product
     */
    protected $object;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $rule = $this->objectManager->create('Magento\CatalogWidget\Model\Rule');
        $this->object = $this->objectManager->create(
            'Magento\CatalogWidget\Model\Rule\Condition\Product'
        );
        $this->object->setRule($rule);
    }

    public function testLoadAttributeOptions()
    {
        $this->object->loadAttributeOptions();
        $options = $this->object->getAttributeOption();
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
        $collection = $this->objectManager->create('Magento\Catalog\Model\Resource\Product\Collection');
        $this->object->setAttribute('special_price');
        $this->object->addToCollection($collection);
        $collectedAttributes = $this->object->getRule()->getCollectedAttributes();
        $this->assertArrayHasKey('special_price', $collectedAttributes);
        $query = (string)$collection->getSelect();
        $this->assertContains('special_price', $query);
        $this->assertEquals('at_special_price.value', $this->object->getMappedSqlField());
    }

    public function testAddNonGlobalAttributeToCollectionNoProducts()
    {
        $collection = $this->objectManager->create('Magento\Catalog\Model\Resource\Product\Collection');
        $this->object->setAttribute('visibility');
        $this->object->setOperator('()');
        $this->object->setValue('4');
        $this->object->addToCollection($collection);
        $collectedAttributes = $this->object->getRule()->getCollectedAttributes();
        $this->assertArrayHasKey('visibility', $collectedAttributes);
        $query = (string)$collection->getSelect();
        $this->assertNotContains('visibility', $query);
        $this->assertEquals('', $this->object->getMappedSqlField());
        $this->assertFalse($this->object->hasValueParsed());
        $this->assertFalse($this->object->hasValue());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testAddNonGlobalAttributeToCollection()
    {
        $collection = $this->objectManager->create('Magento\Catalog\Model\Resource\Product\Collection');
        $this->object->setAttribute('visibility');
        $this->object->setOperator('()');
        $this->object->setValue('4');
        $this->object->addToCollection($collection);
        $collectedAttributes = $this->object->getRule()->getCollectedAttributes();
        $this->assertArrayHasKey('visibility', $collectedAttributes);
        $query = (string)$collection->getSelect();
        $this->assertNotContains('visibility', $query);
        $this->assertEquals('e.entity_id', $this->object->getMappedSqlField());
    }
}

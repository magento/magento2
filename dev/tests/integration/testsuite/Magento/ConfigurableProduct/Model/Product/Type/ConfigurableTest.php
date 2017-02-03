<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\ConfigurableProduct\Model\Product\Type;

/**
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
 */
class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object under test
     *
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    protected function setUp()
    {
        $this->_product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $this->_product->load(1);
        // fixture

        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable'
        );
        // prevent fatal errors by assigning proper "singleton" of type instance to the product
        $this->_product->setTypeInstance($this->_model);
    }

    public function testGetRelationInfo()
    {
        $info = $this->_model->getRelationInfo();
        $this->assertInstanceOf('Magento\Framework\DataObject', $info);
        $this->assertEquals('catalog_product_super_link', $info->getTable());
        $this->assertEquals('parent_id', $info->getParentFieldName());
        $this->assertEquals('product_id', $info->getChildFieldName());
    }

    public function testGetChildrenIds()
    {
        $ids = $this->_model->getChildrenIds(1);
        // fixture
        $this->assertArrayHasKey(0, $ids);
        $this->assertTrue(2 === count($ids[0]));

        $ids = $this->_model->getChildrenIds(1, false);
        $this->assertArrayHasKey(0, $ids);
        $this->assertTrue(2 === count($ids[0]));
    }

    public function testCanUseAttribute()
    {
        $this->assertFalse($this->_model->canUseAttribute($this->_getAttributeByCode('sku')));
        $this->assertTrue($this->_model->canUseAttribute($this->_getAttributeByCode('test_configurable')));
    }

    public function testSetGetUsedProductAttributeIds()
    {
        $testConfigurable = $this->_getAttributeByCode('test_configurable');
        $actual = $this->_model->getUsedProductAttributeIds($this->_product);
        $expected = [$testConfigurable->getId()];
        $this->assertEquals($expected, $actual);
    }

    public function testSetUsedProductAttributeIds()
    {
        $testConfigurable = $this->_getAttributeByCode('test_configurable');
        $this->_model->setUsedProductAttributeIds([$testConfigurable->getId()], $this->_product);
        $attributes = $this->_product->getData('_cache_instance_configurable_attributes');
        $this->assertArrayHasKey(0, $attributes);
        $this->assertInstanceOf(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute',
            $attributes[0]
        );
        $this->assertSame($testConfigurable, $attributes[0]->getProductAttribute());
    }

    public function testGetUsedProductAttributes()
    {
        $testConfigurable = $this->_getAttributeByCode('test_configurable');
        $attributeId = (int)$testConfigurable->getId();
        $attributes = $this->_model->getUsedProductAttributes($this->_product);
        $this->assertArrayHasKey($attributeId, $attributes);
        $this->assertSame($testConfigurable, $attributes[$attributeId]);
    }

    public function testGetConfigurableAttributes()
    {
        $collection = $this->_model->getConfigurableAttributes($this->_product);
        $this->assertInstanceOf(
            'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection',
            $collection
        );
        $testConfigurable = $this->_getAttributeByCode('test_configurable');
        foreach ($collection as $attribute) {
            $this->assertInstanceOf(
                'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute',
                $attribute
            );
            $this->assertEquals($testConfigurable->getId(), $attribute->getAttributeId());
            $options = $attribute->getOptions();
            $this->assertCount(2, $options);
            // fixture
            $this->assertEquals('Option 1', $options[0]['label']);
            $this->assertEquals('Option 2', $options[1]['label']);
            break;
        }
    }

    public function testGetConfigurableAttributesAsArray()
    {
        $attributes = $this->_model->getConfigurableAttributesAsArray($this->_product);
        $attribute = reset($attributes);
        $this->assertArrayHasKey('id', $attribute);
        $this->assertArrayHasKey('label', $attribute);
        $this->assertArrayHasKey('use_default', $attribute);
        $this->assertArrayHasKey('position', $attribute);
        $this->assertArrayHasKey('values', $attribute);
        $this->assertArrayHasKey(0, $attribute['values']);
        $this->assertArrayHasKey(1, $attribute['values']);
        foreach ($attribute['values'] as $attributeOption) {
            $this->assertArrayHasKey('product_super_attribute_id', $attributeOption);
            $this->assertArrayHasKey('value_index', $attributeOption);
            $this->assertArrayHasKey('label', $attributeOption);
            $this->assertArrayHasKey('default_label', $attributeOption);
            $this->assertArrayHasKey('store_label', $attributeOption);
            $this->assertArrayHasKey('use_default_value', $attributeOption);
        }
        $this->assertArrayHasKey('attribute_id', $attribute);
        $this->assertArrayHasKey('attribute_code', $attribute);
        $this->assertArrayHasKey('frontend_label', $attribute);
        $this->assertArrayHasKey('store_label', $attribute);

        $testConfigurable = $this->_getAttributeByCode('test_configurable');
        $this->assertEquals($testConfigurable->getId(), $attribute['attribute_id']);
    }

    /**
     * @depends testGetConfigurableAttributesAsArray
     */
    public function testGetParentIdsByChild()
    {
        $result = $this->_model->getParentIdsByChild(10);
        // fixture
        $this->assertEquals([1], $result);
    }

    public function testGetConfigurableAttributeCollection()
    {
        $collection = $this->_model->getConfigurableAttributeCollection($this->_product);
        $this->assertInstanceOf(
            'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection',
            $collection
        );
    }

    public function testGetUsedProductIds()
    {
        $ids = $this->_model->getUsedProductIds($this->_product);
        $this->assertInternalType('array', $ids);
        $this->assertTrue(2 === count($ids)); // impossible to check actual IDs, they are dynamic in the fixture
    }

    public function testGetUsedProducts()
    {
        $products = $this->_model->getUsedProducts($this->_product);
        $this->assertInternalType('array', $products);
        $this->assertTrue(2 === count($products));
        foreach ($products as $product) {
            $this->assertInstanceOf('Magento\Catalog\Model\Product', $product);
        }
    }

    public function testGetUsedProductCollection()
    {
        $this->assertInstanceOf(
            'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection',
            $this->_model->getUsedProductCollection($this->_product)
        );
    }

    public function testBeforeSave()
    {
        $this->assertEmpty($this->_product->getTypeHasOptions());
        $this->assertEmpty($this->_product->getTypeHasRequiredOptions());

        $this->_product->setCanSaveConfigurableAttributes(true);
        $this->_product->setConfigurableAttributesData([['values' => 'not empty']]);
        $this->_model->beforeSave($this->_product);
        $this->assertTrue($this->_product->getTypeHasOptions());
        $this->assertTrue($this->_product->getTypeHasRequiredOptions());
    }

    public function testIsSalable()
    {
        $this->_product->unsetData('is_salable');
        $this->assertTrue($this->_model->isSalable($this->_product));
    }

    /**
     * @depends testGetConfigurableAttributesAsArray
     */
    public function testGetProductByAttributes()
    {
        $attributes = $this->_model->getConfigurableAttributesAsArray($this->_product);
        $attribute = reset($attributes);
        $optionValueId = $attribute['values'][0]['value_index'];

        $product = $this->_model->getProductByAttributes(
            [$attribute['attribute_id'] => $optionValueId],
            $this->_product
        );
        $this->assertInstanceOf('Magento\Catalog\Model\Product', $product);
        $this->assertEquals("simple_10", $product->getSku());
    }

    /**
     * @depends testGetConfigurableAttributesAsArray
     */
    public function testGetSelectedAttributesInfo()
    {
        $attributes = $this->_model->getConfigurableAttributesAsArray($this->_product);
        $attribute = reset($attributes);
        $optionValueId = $attribute['values'][0]['value_index'];

        $this->_product->addCustomOption('attributes', serialize([$attribute['attribute_id'] => $optionValueId]));
        $info = $this->_model->getSelectedAttributesInfo($this->_product);
        $this->assertEquals('Test Configurable', $info[0]['label']);
        $this->assertEquals('Option 1', $info[0]['value']);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetSelectedAttributesInfoForStore()
    {
        $attributes = $this->_model->getConfigurableAttributesAsArray($this->_product);

        $attribute = reset($attributes);
        $optionValueId = $attribute['values'][0]['value_index'];

        $this->_product->addCustomOption('attributes', serialize([$attribute['attribute_id'] => $optionValueId]));

        $configurableAttr = $this->_model->getConfigurableAttributes($this->_product);
        $attribute = $configurableAttr->getFirstItem();

        $attribute->getProductAttribute()->setStoreLabel('store label');
        $info = $this->_model->getSelectedAttributesInfo($this->_product);
        $this->assertEquals('store label', $info[0]['label']);
        $this->assertEquals('Option 1', $info[0]['value']);
    }

    /**
     * @depends testGetConfigurableAttributesAsArray
     */
    public function testPrepareForCart()
    {
        $attributes = $this->_model->getConfigurableAttributesAsArray($this->_product);
        $attribute = reset($attributes);
        $optionValueId = $attribute['values'][0]['value_index'];

        $buyRequest = new \Magento\Framework\DataObject(
            ['qty' => 5, 'super_attribute' => [$attribute['attribute_id'] => $optionValueId]]
        );
        $result = $this->_model->prepareForCart($buyRequest, $this->_product);
        $this->assertInternalType('array', $result);
        $this->assertTrue(2 === count($result));
        foreach ($result as $product) {
            $this->assertInstanceOf('Magento\Catalog\Model\Product', $product);
        }
        $this->assertInstanceOf('Magento\Framework\DataObject', $result[1]->getCustomOption('parent_product_id'));
    }

    public function testGetSpecifyOptionMessage()
    {
        $this->assertEquals(
            'You need to choose options for your item.',
            (string)$this->_model->getSpecifyOptionMessage()
        );
    }

    /**
     * @depends testGetConfigurableAttributesAsArray
     * @depends testPrepareForCart
     */
    public function testGetOrderOptions()
    {
        $this->_prepareForCart();

        $result = $this->_model->getOrderOptions($this->_product);
        $this->assertArrayHasKey('info_buyRequest', $result);
        $this->assertArrayHasKey('attributes_info', $result);
        $this->assertEquals('Test Configurable', $result['attributes_info'][0]['label']);
        $this->assertEquals('Option 1', $result['attributes_info'][0]['value']);
        $this->assertArrayHasKey('product_calculations', $result);
        $this->assertArrayHasKey('shipment_type', $result);
        $this->assertEquals(
            \Magento\Catalog\Model\Product\Type\AbstractType::CALCULATE_PARENT,
            $result['product_calculations']
        );
        $this->assertEquals(
            \Magento\Catalog\Model\Product\Type\AbstractType::SHIPMENT_TOGETHER,
            $result['shipment_type']
        );
    }

    /**
     * @depends testGetConfigurableAttributesAsArray
     * @depends testPrepareForCart
     */
    public function testIsVirtual()
    {
        $this->_prepareForCart();
        $this->assertFalse($this->_model->isVirtual($this->_product));
    }

    public function testHasOptions()
    {
        $this->assertTrue($this->_model->hasOptions($this->_product));
    }

    public function testGetWeight()
    {
        $this->assertEmpty($this->_model->getWeight($this->_product));

        $this->_product->setCustomOptions(
            [
                'simple_product' => new \Magento\Framework\DataObject(
                        [
                            'product' => new \Magento\Framework\DataObject(['weight' => 2]),
                        ]
                    ),
            ]
        );
        $this->assertEquals(2, $this->_model->getWeight($this->_product));
    }

    public function testAssignProductToOption()
    {
        $option = new \Magento\Framework\DataObject();
        $this->_model->assignProductToOption('test', $option, $this->_product);
        $this->assertEquals('test', $option->getProduct());
        // other branch of logic depends on \Magento\Sales module
    }

    public function testGetProductsToPurchaseByReqGroups()
    {
        $result = $this->_model->getProductsToPurchaseByReqGroups($this->_product);
        $this->assertArrayHasKey(0, $result);
        $this->assertInternalType('array', $result[0]);
        $this->assertTrue(2 === count($result[0]));
        // fixture has 2 simple products
        foreach ($result[0] as $product) {
            $this->assertInstanceOf('Magento\Catalog\Model\Product', $product);
        }
    }

    public function testGetSku()
    {
        $this->assertEquals('configurable', $this->_model->getSku($this->_product));
        $this->_prepareForCart();
        $this->assertStringStartsWith('simple_', $this->_model->getSku($this->_product));
    }

    public function testProcessBuyRequest()
    {
        $buyRequest = new \Magento\Framework\DataObject(['super_attribute' => ['10', 'string']]);
        $result = $this->_model->processBuyRequest($this->_product, $buyRequest);
        $this->assertEquals(['super_attribute' => [10]], $result);
    }

    public function testSaveProductRelationsOneChild()
    {
        $oldChildrenIds = $this->_product->getTypeInstance()->getChildrenIds(1);
        $oldChildrenIds = reset($oldChildrenIds);
        $oneChildId = reset($oldChildrenIds);
        $this->assertNotEmpty($oldChildrenIds);
        $this->assertNotEmpty($oneChildId);

        $this->_product->setAssociatedProductIds([$oneChildId]);
        $this->_model->save($this->_product);
        $this->_product->load(1);

        $this->assertEquals(
            [[$oneChildId => $oneChildId]],
            $this->_product->getTypeInstance()->getChildrenIds(1)
        );
    }

    public function testSaveProductRelationsNoChildren()
    {
        $childrenIds = $this->_product->getTypeInstance()->getChildrenIds(1);
        $this->assertNotEmpty(reset($childrenIds));

        $this->_product->setAssociatedProductIds([]);
        $this->_model->save($this->_product);
        $this->_product->load(1);

        $this->assertEquals([[]], $this->_product->getTypeInstance()->getChildrenIds(1));
    }

    /**
     * Find and instantiate a catalog attribute model by attribute code
     *
     * @param string $code
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected function _getAttributeByCode($code)
    {
        return \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Eav\Model\Config'
        )->getAttribute(
            'catalog_product',
            $code
        );
    }

    /**
     * Select one of the options and "prepare for cart" with a proper buy request
     */
    protected function _prepareForCart()
    {
        $attributes = $this->_model->getConfigurableAttributesAsArray($this->_product);
        $attribute = reset($attributes);
        $optionValueId = $attribute['values'][0]['value_index'];

        $buyRequest = new \Magento\Framework\DataObject(
            ['qty' => 5, 'super_attribute' => [$attribute['attribute_id'] => $optionValueId]]
        );
        $this->_model->prepareForCart($buyRequest, $this->_product);
    }
}

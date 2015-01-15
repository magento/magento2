<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Type;

/**
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
        $this->assertInstanceOf('Magento\Framework\Object', $info);
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
        $this->assertEmpty($this->_product->getData('_cache_instance_configurable_attributes'));
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
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Collection',
            $collection
        );
        $testConfigurable = $this->_getAttributeByCode('test_configurable');
        foreach ($collection as $attribute) {
            $this->assertInstanceOf(
                'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute',
                $attribute
            );
            $this->assertEquals($testConfigurable->getId(), $attribute->getAttributeId());
            $prices = $attribute->getPrices();
            $this->assertCount(2, $prices);
            // fixture
            $this->assertArrayHasKey('pricing_value', $prices[0]);
            $this->assertEquals('Option 1', $prices[0]['label']);
            $this->assertEquals(5, $prices[0]['pricing_value']);
            $this->assertEquals('Option 2', $prices[1]['label']);
            $this->assertEquals(5, $prices[1]['pricing_value']);
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
            $this->assertArrayHasKey('is_percent', $attributeOption);
            $this->assertArrayHasKey('pricing_value', $attributeOption);
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
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Collection',
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
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Product\Collection',
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
        $this->assertEquals([['label' => 'Test Configurable', 'value' => 'Option 1']], $info);
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
        $this->assertEquals([['label' => 'store label', 'value' => 'Option 1']], $info);
    }

    /**
     * @depends testGetConfigurableAttributesAsArray
     */
    public function testPrepareForCart()
    {
        $attributes = $this->_model->getConfigurableAttributesAsArray($this->_product);
        $attribute = reset($attributes);
        $optionValueId = $attribute['values'][0]['value_index'];

        $buyRequest = new \Magento\Framework\Object(
            ['qty' => 5, 'super_attribute' => [$attribute['attribute_id'] => $optionValueId]]
        );
        $result = $this->_model->prepareForCart($buyRequest, $this->_product);
        $this->assertInternalType('array', $result);
        $this->assertTrue(2 === count($result));
        foreach ($result as $product) {
            $this->assertInstanceOf('Magento\Catalog\Model\Product', $product);
        }
        $this->assertInstanceOf('Magento\Framework\Object', $result[1]->getCustomOption('parent_product_id'));
    }

    public function testGetSpecifyOptionMessage()
    {
        $this->assertEquals('Please specify the product\'s option(s).', $this->_model->getSpecifyOptionMessage());
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
        $this->assertEquals(
            [['label' => 'Test Configurable', 'value' => 'Option 1']],
            $result['attributes_info']
        );
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
                'simple_product' => new \Magento\Framework\Object(
                        [
                            'product' => new \Magento\Framework\Object(['weight' => 2]),
                        ]
                    ),
            ]
        );
        $this->assertEquals(2, $this->_model->getWeight($this->_product));
    }

    public function testAssignProductToOption()
    {
        $option = new \Magento\Framework\Object();
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
        $buyRequest = new \Magento\Framework\Object(['super_attribute' => ['10', 'string']]);
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
     * @param array $productsData
     * @dataProvider generateSimpleProductsDataProvider
     */
    public function testGenerateSimpleProducts($productsData)
    {
        $this->_product->setNewVariationsAttributeSetId(4);
        // Default attribute set id
        $generatedProducts = $this->_model->generateSimpleProducts($this->_product, $productsData);
        $this->assertEquals(3, count($generatedProducts));
        foreach ($generatedProducts as $productId) {
            /** @var $product \Magento\Catalog\Model\Product */
            $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\Catalog\Model\Product'
            );
            $product->load($productId);
            $this->assertNotNull($product->getName());
            $this->assertNotNull($product->getSku());
            $this->assertNotNull($product->getPrice());
            $this->assertNotNull($product->getWeight());
        }
    }

    /**
     * @param array $productsData
     * @dataProvider generateSimpleProductsWithPartialDataDataProvider
     * @magentoDbIsolation enabled
     */
    public function testGenerateSimpleProductsWithPartialData($productsData)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry */
        $stockRegistry = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface');
        $this->_product->setNewVariationsAttributeSetId(4);
        $generatedProducts = $this->_model->generateSimpleProducts($this->_product, $productsData);
        foreach ($generatedProducts as $productId) {
            $stockItem = $stockRegistry->getStockItem($productId);
            $this->assertEquals('0', $stockItem->getManageStock());
            $this->assertEquals('1', $stockItem->getIsInStock());
        }
    }

    /**
     * @return array
     */
    public static function generateSimpleProductsDataProvider()
    {
        return [
            [
                [
                    [
                        'name' => '1-aaa',
                        'configurable_attribute' => '{"configurable_attribute":"25"}',
                        'price' => '3',
                        'sku' => '1-aaa',
                        'quantity_and_stock_status' => ['qty' => '5'],
                        'weight' => '6',
                    ],
                    [
                        'name' => '1-bbb',
                        'configurable_attribute' => '{"configurable_attribute":"24"}',
                        'price' => '3',
                        'sku' => '1-bbb',
                        'quantity_and_stock_status' => ['qty' => '5'],
                        'weight' => '6'
                    ],
                    [
                        'name' => '1-ccc',
                        'configurable_attribute' => '{"configurable_attribute":"23"}',
                        'price' => '3',
                        'sku' => '1-ccc',
                        'quantity_and_stock_status' => ['qty' => '5'],
                        'weight' => '6'
                    ],
                ],
            ]
        ];
    }

    /**
     * @return array
     */
    public static function generateSimpleProductsWithPartialDataDataProvider()
    {
        return [
            [
                [
                    [
                        'name' => '1-aaa',
                        'configurable_attribute' => '{"configurable_attribute":"23"}',
                        'price' => '3',
                        'sku' => '1-aaa-1',
                        'quantity_and_stock_status' => ['qty' => ''],
                        'weight' => '6',
                    ],
                ],
            ]
        ];
    }

    /**
     * Find and instantiate a catalog attribute model by attribute code
     *
     * @param string $code
     * @return \Magento\Catalog\Model\Resource\Eav\Attribute
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

        $buyRequest = new \Magento\Framework\Object(
            ['qty' => 5, 'super_attribute' => [$attribute['attribute_id'] => $optionValueId]]
        );
        $this->_model->prepareForCart($buyRequest, $this->_product);
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\ConfigurableProduct\Model\Product\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Stock\Status;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class ConfigurableTest
 *
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object under test
     *
     * @var Configurable
     */
    private $model;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()
            ->create(ProductRepositoryInterface::class);

        $this->product = $this->productRepository->get('configurable');

        $this->model = Bootstrap::getObjectManager()
            ->create(Configurable::class);

        // prevent fatal errors by assigning proper "singleton" of type instance to the product
        $this->product->setTypeInstance($this->model);
    }

    public function testGetRelationInfo()
    {
        $info = $this->model->getRelationInfo();
        $this->assertInstanceOf('Magento\Framework\DataObject', $info);
        $this->assertEquals('catalog_product_super_link', $info->getTable());
        $this->assertEquals('parent_id', $info->getParentFieldName());
        $this->assertEquals('product_id', $info->getChildFieldName());
    }

    public function testGetChildrenIds()
    {
        $ids = $this->model->getChildrenIds($this->product->getId());
        // fixture
        $this->assertArrayHasKey(0, $ids);
        $this->assertTrue(2 === count($ids[0]));

        $ids = $this->model->getChildrenIds($this->product->getId(), false);
        $this->assertArrayHasKey(0, $ids);
        $this->assertTrue(2 === count($ids[0]));
    }

    public function testCanUseAttribute()
    {
        $this->assertFalse($this->model->canUseAttribute($this->_getAttributeByCode('sku')));
        $this->assertTrue($this->model->canUseAttribute($this->_getAttributeByCode('test_configurable')));
    }

    public function testSetGetUsedProductAttributeIds()
    {
        $testConfigurable = $this->_getAttributeByCode('test_configurable');
        $actual = $this->model->getUsedProductAttributeIds($this->product);
        $expected = [$testConfigurable->getId()];
        $this->assertEquals($expected, $actual);
    }

    public function testSetUsedProductAttributeIds()
    {
        $testConfigurable = $this->_getAttributeByCode('test_configurable');
        $this->model->setUsedProductAttributeIds([$testConfigurable->getId()], $this->product);
        $attributes = $this->product->getData('_cache_instance_configurable_attributes');
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
        $attributes = $this->model->getUsedProductAttributes($this->product);
        $this->assertArrayHasKey($attributeId, $attributes);
        $this->assertEquals($testConfigurable->getData(), $attributes[$attributeId]->getData());
    }

    public function testGetConfigurableAttributes()
    {
        $collection = $this->model->getConfigurableAttributes($this->product);
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
        $product = $this->productRepository->get('configurable', true);
        $attributes = $this->model->getConfigurableAttributesAsArray($product);
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
        $result = $this->model->getParentIdsByChild(10);
        // fixture
        $this->assertEquals([$this->product->getId()], $result);
    }

    public function testGetConfigurableAttributeCollection()
    {
        $collection = $this->model->getConfigurableAttributeCollection($this->product);
        $this->assertInstanceOf(
            'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection',
            $collection
        );
    }

    public function testGetUsedProductIds()
    {
        $ids = $this->model->getUsedProductIds($this->product);
        $this->assertInternalType('array', $ids);
        $this->assertTrue(2 === count($ids)); // impossible to check actual IDs, they are dynamic in the fixture
    }

    public function testGetUsedProducts()
    {
        $products = $this->model->getUsedProducts($this->product);
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
            $this->model->getUsedProductCollection($this->product)
        );
    }

    public function testBeforeSave()
    {
        $this->assertEmpty($this->product->getTypeHasOptions());
        $this->assertEmpty($this->product->getTypeHasRequiredOptions());

        $this->product->setCanSaveConfigurableAttributes(true);
        $this->product->setConfigurableAttributesData([['values' => 'not empty']]);
        $this->model->beforeSave($this->product);
        $this->assertTrue($this->product->getTypeHasOptions());
        $this->assertTrue($this->product->getTypeHasRequiredOptions());
    }

    public function testIsSalable()
    {
        $this->product->unsetData('is_salable');
        $this->assertTrue($this->model->isSalable($this->product));
    }

    /**
     * @depends testGetConfigurableAttributesAsArray
     */
    public function testGetProductByAttributes()
    {
        $attributes = $this->model->getConfigurableAttributesAsArray($this->product);
        $attribute = reset($attributes);
        $optionValueId = $attribute['values'][0]['value_index'];

        $product = $this->model->getProductByAttributes(
            [$attribute['attribute_id'] => $optionValueId],
            $this->product
        );
        $this->assertInstanceOf('Magento\Catalog\Model\Product', $product);
        $this->assertEquals("simple_10", $product->getSku());
    }

    /**
     * @depends testGetConfigurableAttributesAsArray
     */
    public function testGetSelectedAttributesInfo()
    {
        $product = $this->productRepository->get('configurable', true);
        $attributes = $this->model->getConfigurableAttributesAsArray($product);
        $attribute = reset($attributes);
        $optionValueId = $attribute['values'][0]['value_index'];

        $product->addCustomOption('attributes', serialize([$attribute['attribute_id'] => $optionValueId]));
        $info = $this->model->getSelectedAttributesInfo($product);
        $this->assertEquals([['label' => 'Test Configurable', 'value' => 'Option 1',
            'option_id' => $attribute['attribute_id'], 'option_value' => $attribute['values'][0]['value_index']]],
            $info);
    }

    public function testGetSelectedAttributesInfoForStore()
    {
        $attributes = $this->model->getConfigurableAttributesAsArray($this->product);

        $attribute = reset($attributes);
        $optionValueId = $attribute['values'][0]['value_index'];

        $this->product->addCustomOption('attributes', serialize([$attribute['attribute_id'] => $optionValueId]));

        $configurableAttr = $this->model->getConfigurableAttributes($this->product);
        $attribute = $configurableAttr->getFirstItem();

        $attribute->getProductAttribute()->setStoreLabel('store label');
        $info = $this->model->getSelectedAttributesInfo($this->product);
        $this->assertEquals([['label' => 'store label', 'value' => 'Option 1',
            'option_id' => $attribute['attribute_id'], 'option_value' => $optionValueId]],
            $info);
    }

    /**
     * @depends testGetConfigurableAttributesAsArray
     */
    public function testPrepareForCart()
    {
        $attributes = $this->model->getConfigurableAttributesAsArray($this->product);
        $attribute = reset($attributes);
        $optionValueId = $attribute['values'][0]['value_index'];

        $buyRequest = new \Magento\Framework\DataObject(
            ['qty' => 5, 'super_attribute' => [$attribute['attribute_id'] => $optionValueId]]
        );
        $result = $this->model->prepareForCart($buyRequest, $this->product);
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
            (string)$this->model->getSpecifyOptionMessage()
        );
    }

    /**
     * @depends testGetConfigurableAttributesAsArray
     * @depends testPrepareForCart
     */
    public function testGetOrderOptions()
    {
        $product = $this->_prepareForCart();

        $result = $this->model->getOrderOptions($product);

        $attributes = $this->model->getConfigurableAttributesAsArray($this->product);
        $attribute = reset($attributes);

        $this->assertArrayHasKey('info_buyRequest', $result);
        $this->assertArrayHasKey('attributes_info', $result);
        $this->assertEquals(
            [['label' => 'Test Configurable', 'value' => 'Option 1',
                'option_id' => $attribute['attribute_id'], 'option_value' => $attribute['values'][0]['value_index']]],
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
        $product = $this->_prepareForCart();
        $this->assertFalse($this->model->isVirtual($product));
    }

    public function testHasOptions()
    {
        $this->assertTrue($this->model->hasOptions($this->product));
    }

    public function testGetWeight()
    {
        $this->assertEmpty($this->model->getWeight($this->product));

        $this->product->setCustomOptions(
            [
                'simple_product' => new \Magento\Framework\DataObject(
                    [
                        'product' => new \Magento\Framework\DataObject(['weight' => 2]),
                    ]
                ),
            ]
        );
        $this->assertEquals(2, $this->model->getWeight($this->product));
    }

    public function testAssignProductToOption()
    {
        $option = new \Magento\Framework\DataObject();
        $this->model->assignProductToOption('test', $option, $this->product);
        $this->assertEquals('test', $option->getProduct());
        // other branch of logic depends on \Magento\Sales module
    }

    public function testGetProductsToPurchaseByReqGroups()
    {
        $result = $this->model->getProductsToPurchaseByReqGroups($this->product);
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
        $this->assertEquals('configurable', $this->model->getSku($this->product));
        $product = $this->_prepareForCart();
        $this->assertStringStartsWith('simple_', $this->model->getSku($product));
    }

    public function testProcessBuyRequest()
    {
        $buyRequest = new \Magento\Framework\DataObject(['super_attribute' => ['10', 'string']]);
        $result = $this->model->processBuyRequest($this->product, $buyRequest);
        $this->assertEquals(['super_attribute' => [10]], $result);
    }

    public function testSaveProductRelationsOneChild()
    {
        $oldChildrenIds = $this->product->getTypeInstance()
            ->getChildrenIds($this->product->getId());

        $oldChildrenIds = reset($oldChildrenIds);
        $oneChildId = reset($oldChildrenIds);

        self::assertNotEmpty($oldChildrenIds);
        self::assertNotEmpty($oneChildId);

        $product = $this->productRepository->getById($this->product->getId());

        $extensionAttributes = $product->getExtensionAttributes();
        $extensionAttributes->setConfigurableProductLinks([$oneChildId]);
        $product->setExtensionAttributes($extensionAttributes);

        $this->productRepository->save($product);

        self::assertEquals(
            [
                [
                    $oneChildId => $oneChildId
                ]
            ],
            $this->model->getChildrenIds($this->product->getId())
        );
    }

    public function testSaveProductRelationsNoChildren()
    {
        $childrenIds = $this->product->getTypeInstance()
            ->getChildrenIds($this->product->getId());

        self::assertNotEmpty(reset($childrenIds));

        $product = $this->productRepository->getById($this->product->getId(), true);

        $extensionAttributes = $product->getExtensionAttributes();
        $extensionAttributes->setConfigurableProductLinks([]);
        $product->setExtensionAttributes($extensionAttributes);

        $this->productRepository->save($product);

        self::assertEquals(
            [
                []
            ],
            $this->model->getChildrenIds($this->product->getId())
        );
    }

    /**
     * Find and instantiate a catalog attribute model by attribute code
     *
     * @param string $code
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected function _getAttributeByCode($code)
    {
        return Bootstrap::getObjectManager()->get(
            'Magento\Eav\Model\Config'
        )->getAttribute(
            'catalog_product',
            $code
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @dataProvider allowProductsDataProvider
     */
    public function testGetSalableUsedProducts($isInStock, $status, $expectedCount)
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $childProduct = $productRepository->get('simple_10');
        $childProduct->setStatus($status);
        $stockItem = $childProduct->getExtensionAttributes()->getStockItem();
        $stockItem->setIsInStock($isInStock);
        $productRepository->save($childProduct);

        $products = $this->model->getSalableUsedProducts($this->product);
        $this->assertCount($expectedCount, $products);
        foreach ($products as $product) {
            $this->assertInstanceOf('Magento\Catalog\Model\Product', $product);
        }
    }

    /**
     * @return array
     */
    public function allowProductsDataProvider()
    {
        return [
            [Status::STATUS_OUT_OF_STOCK, false, 1],
            [Status::STATUS_OUT_OF_STOCK, true, 1],
            [Status::STATUS_IN_STOCK, false, 1],
            [Status::STATUS_IN_STOCK, true, 2],
        ];
    }

    /**
     * Select one of the options and "prepare for cart" with a proper buy request
     *
     * @return ProductInterface
     */
    protected function _prepareForCart()
    {
        $product = $this->productRepository->get('configurable', true);
        $attributes = $this->model->getConfigurableAttributesAsArray($product);
        $attribute = reset($attributes);
        $optionValueId = $attribute['values'][0]['value_index'];

        $buyRequest = new \Magento\Framework\DataObject(
            ['qty' => 5, 'super_attribute' => [$attribute['attribute_id'] => $optionValueId]]
        );
        $this->model->prepareForCart($buyRequest, $product);

        return $product;
    }
}

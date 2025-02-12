<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Attribute as AttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Eav\Model\GetAttributeSetByName;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;

/**
 * Tests product resource model
 *
 * @see \Magento\Catalog\Model\ResourceModel\Product
 * @see \Magento\Catalog\Model\ResourceModel\AbstractResource
 */
class ProductTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Product
     */
    private $model;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->model = $this->objectManager->create(Product::class);

        $this->storeManager = $this->objectManager->create(StoreManagerInterface::class);
    }

    /**
     * Checks a possibility to retrieve product raw attribute value.
     */
    #[
        DataFixture(ProductFixture::class, ['sku' => 'simple']),
    ]
    public function testGetAttributeRawValue()
    {
        $sku = 'simple';
        $attribute = 'name';

        $product = $this->productRepository->get($sku);
        $actual = $this->model->getAttributeRawValue($product->getId(), $attribute, null);
        self::assertEquals($product->getName(), $actual);
    }

    /**
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    #[
        AppArea('adminhtml'),
        DataFixture(AttributeFixture::class, ['attribute_code' => 'prod_attr']),
        DataFixture(ProductFixture::class, ['sku' => 'simple']),
    ]
    public function testGetAttributeRawValueGetDefault()
    {
        $product = $this->productRepository->get('simple', true, 0, true);
        $product->setCustomAttribute('prod_attr', 'default_value');
        $this->productRepository->save($product);

        $actual = $this->model->getAttributeRawValue($product->getId(), 'prod_attr', 1);
        $this->assertEquals('default_value', $actual);
    }

    /**
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    #[
        AppArea('adminhtml'),
        DataFixture(AttributeFixture::class, ['attribute_code' => 'prod_attr']),
        DataFixture(ProductFixture::class, ['sku' => 'simple']),
    ]
    public function testGetAttributeRawValueGetStoreSpecificValueNoDefault()
    {
        $product = $this->productRepository->get('simple', true, 0, true);
        $product->setCustomAttribute('prod_attr', null);
        $this->productRepository->save($product);

        $product = $this->productRepository->get('simple', true, 1, true);
        $product->setCustomAttribute('prod_attr', 'store_value');
        $this->productRepository->save($product);

        $actual = $this->model->getAttributeRawValue($product->getId(), 'prod_attr', 1);
        $this->assertEquals('store_value', $actual);
    }

    /**
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    #[
        AppArea('adminhtml'),
        DataFixture(AttributeFixture::class, ['attribute_code' => 'prod_attr']),
        DataFixture(ProductFixture::class, ['sku' => 'simple']),
    ]
    public function testGetAttributeRawValueGetStoreSpecificValueWithDefault()
    {
        $product = $this->productRepository->get('simple', true, 0, true);
        $product->setCustomAttribute('prod_attr', 'default_value');
        $this->productRepository->save($product);

        $product = $this->productRepository->get('simple', true, 1, true);
        $product->setCustomAttribute('prod_attr', 'store_value');
        $this->productRepository->save($product);

        $actual = $this->model->getAttributeRawValue($product->getId(), 'prod_attr', 1);
        $this->assertEquals('store_value', $actual);
    }

    /**
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     * @throws NoSuchEntityException
     */
    #[
        AppArea('adminhtml'),
        DataFixture(AttributeFixture::class, ['attribute_code' => 'prod_attr']),
        DataFixture(ProductFixture::class, ['sku' => 'simple']),
    ]
    public function testGetAttributeRawValueGetStoreValueFallbackToDefault()
    {
        $product = $this->productRepository->get('simple', true, 0, true);
        $product->setCustomAttribute('prod_attr', 'default_value');
        $this->productRepository->save($product);

        $actual = $this->model->getAttributeRawValue($product->getId(), 'prod_attr', 1);
        $this->assertEquals('default_value', $actual);
    }

    #[
        AppArea('adminhtml'),
        AppIsolation(true),
        ConfigFixture('catalog/price/scope', '1', 'store'),
        DataFixture(ProductFixture::class, ['sku' => 'simple', 'special_price' => 5.99]),
    ]
    public function testUpdateStoreSpecificSpecialPrice()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple', true, 1);
        $this->assertEquals(5.99, $product->getSpecialPrice());

        $product->setSpecialPrice('');
        $this->model->save($product);
        $product = $this->productRepository->get('simple', false, 1, true);
        $this->assertEmpty($product->getSpecialPrice());

        $product = $this->productRepository->get('simple', false, 0, true);
        $this->assertEquals(5.99, $product->getSpecialPrice());
    }

    /**
     * Checks that product has no attribute values for attributes not assigned to the product's attribute set.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/attribute_set_with_image_attribute.php
     */
    public function testChangeAttributeSet()
    {
        $attributeCode = 'funny_image';
        /** @var GetAttributeSetByName $attributeSetModel */
        $attributeSetModel = $this->objectManager->get(GetAttributeSetByName::class);
        $attributeSet = $attributeSetModel->execute('attribute_set_with_media_attribute');

        $product = $this->productRepository->get('simple', true, 1, true);
        $product->setAttributeSetId($attributeSet->getAttributeSetId());
        $this->productRepository->save($product);
        $product->setData($attributeCode, 'test');
        $this->model->saveAttribute($product, $attributeCode);

        $product = $this->productRepository->get('simple', true, 1, true);
        $this->assertEquals('test', $product->getData($attributeCode));

        $product->setAttributeSetId($product->getDefaultAttributeSetId());
        $this->productRepository->save($product);

        $attribute = $this->model->getAttributeRawValue($product->getId(), $attributeCode, 1);
        $this->assertEmpty($attribute);
    }

    /**
     * Test update product custom attributes
     *
     * @return void
     */
    #[
        DataFixture(AttributeFixture::class, ['attribute_code' => 'first_custom_attribute']),
        DataFixture(AttributeFixture::class, ['attribute_code' => 'second_custom_attribute']),
        DataFixture(AttributeFixture::class, ['attribute_code' => 'third_custom_attribute']),
        DataFixture(ProductFixture::class, ['sku' => 'simple','media_gallery_entries' => [[], []]], as: 'product')
    ]

    public function testUpdateCustomerAttributesAutoIncrement()
    {
        $resource = $this->objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        $connection = $resource->getConnection();
        $currentTableStatus = $connection->showTableStatus('catalog_product_entity_varchar');
        $this->storeManager->setCurrentStore('admin');
        $product = $this->productRepository->get('simple');
        $product->setCustomAttribute(
            'first_custom_attribute',
            'first attribute'
        );
        $firstAttributeSavedProduct = $this->productRepository->save($product);
        $currentTableStatusAfterFirstAttrSave = $connection->showTableStatus('catalog_product_entity_varchar');
        $this->assertSame(
            ((int) ($currentTableStatus['Auto_increment']) + 1),
            (int) $currentTableStatusAfterFirstAttrSave['Auto_increment']
        );

        $firstAttributeSavedProduct->setCustomAttribute(
            'second_custom_attribute',
            'second attribute'
        );
        $secondAttributeSavedProduct = $this->productRepository->save($firstAttributeSavedProduct);
        $currentTableStatusAfterSecondAttrSave = $connection->showTableStatus('catalog_product_entity_varchar');
        $this->assertSame(
            (((int) $currentTableStatusAfterFirstAttrSave['Auto_increment']) + 1),
            (int) $currentTableStatusAfterSecondAttrSave['Auto_increment']
        );

        $secondAttributeSavedProduct->setCustomAttribute(
            'third_custom_attribute',
            'third attribute'
        );
        $this->productRepository->save($secondAttributeSavedProduct);
        $currentTableStatusAfterThirdAttrSave = $connection->showTableStatus('catalog_product_entity_varchar');
        $this->assertSame(
            (((int)$currentTableStatusAfterSecondAttrSave['Auto_increment']) + 1),
            (int) $currentTableStatusAfterThirdAttrSave['Auto_increment']
        );
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
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
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->model = $this->objectManager->create(Product::class);
    }

    /**
     * Checks a possibility to retrieve product raw attribute value.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetAttributeRawValue()
    {
        $sku = 'simple';
        $attribute = 'name';

        $product = $this->productRepository->get($sku);
        $actual = $this->model->getAttributeRawValue($product->getId(), $attribute, null);
        self::assertEquals($product->getName(), $actual);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_custom_store_scope_attribute.php
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    public function testGetAttributeRawValueGetDefault()
    {
        $product = $this->productRepository->get('simple_with_store_scoped_custom_attribute', true, 0, true);
        $product->setCustomAttribute('store_scoped_attribute_code', 'default_value');
        $this->productRepository->save($product);

        $actual = $this->model->getAttributeRawValue($product->getId(), 'store_scoped_attribute_code', 1);
        $this->assertEquals('default_value', $actual);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_custom_store_scope_attribute.php
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    public function testGetAttributeRawValueGetStoreSpecificValueNoDefault()
    {
        $product = $this->productRepository->get('simple_with_store_scoped_custom_attribute', true, 0, true);
        $product->setCustomAttribute('store_scoped_attribute_code', null);
        $this->productRepository->save($product);

        $product = $this->productRepository->get('simple_with_store_scoped_custom_attribute', true, 1, true);
        $product->setCustomAttribute('store_scoped_attribute_code', 'store_value');
        $this->productRepository->save($product);

        $actual = $this->model->getAttributeRawValue($product->getId(), 'store_scoped_attribute_code', 1);
        $this->assertEquals('store_value', $actual);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_custom_store_scope_attribute.php
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    public function testGetAttributeRawValueGetStoreSpecificValueWithDefault()
    {
        $product = $this->productRepository->get('simple_with_store_scoped_custom_attribute', true, 0, true);
        $product->setCustomAttribute('store_scoped_attribute_code', 'default_value');
        $this->productRepository->save($product);

        $product = $this->productRepository->get('simple_with_store_scoped_custom_attribute', true, 1, true);
        $product->setCustomAttribute('store_scoped_attribute_code', 'store_value');
        $this->productRepository->save($product);

        $actual = $this->model->getAttributeRawValue($product->getId(), 'store_scoped_attribute_code', 1);
        $this->assertEquals('store_value', $actual);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_custom_store_scope_attribute.php
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     * @throws NoSuchEntityException
     */
    public function testGetAttributeRawValueGetStoreValueFallbackToDefault()
    {
        $product = $this->productRepository->get('simple_with_store_scoped_custom_attribute', true, 0, true);
        $product->setCustomAttribute('store_scoped_attribute_code', 'default_value');
        $this->productRepository->save($product);

        $actual = $this->model->getAttributeRawValue($product->getId(), 'store_scoped_attribute_code', 1);
        $this->assertEquals('default_value', $actual);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/_files/product_special_price.php
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default_store catalog/price/scope 1
     */
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
}

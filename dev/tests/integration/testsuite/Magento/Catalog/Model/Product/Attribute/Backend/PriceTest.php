<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

/**
 * Test class for \Magento\Catalog\Model\Product\Attribute\Backend\Price.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Backend\Price
     */
    private $model;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Attribute\Backend\Price::class
        );
        $this->model->setAttribute(
            $this->objectManager->get(
                \Magento\Eav\Model\Config::class
            )->getAttribute(
                'catalog_product',
                'price'
            )
        );
    }

    public function testSetScopeDefault()
    {
        /* validate result of setAttribute */
        $this->assertEquals(
            \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            $this->model->getAttribute()->getIsGlobal()
        );
        $this->model->setScope($this->model->getAttribute());
        $this->assertEquals(
            \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            $this->model->getAttribute()->getIsGlobal()
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store catalog/price/scope 1
     */
    public function testSetScope()
    {
        $this->model->setScope($this->model->getAttribute());
        $this->assertEquals(
            \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
            $this->model->getAttribute()->getIsGlobal()
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoConfigFixture current_store catalog/price/scope 1
     * @magentoConfigFixture current_store currency/options/base GBP
     */
    public function testAfterSave()
    {
        $repository = $this->objectManager->create(
            \Magento\Catalog\Model\ProductRepository::class
        );
        $product = $repository->get('simple');
        $product->setOrigData();
        $product->setPrice(9.99);
        $product->setStoreId(0);
        $repository->save($product);
        $this->assertEquals(
            '9.99',
            $product->getResource()->getAttributeRawValue(
                $product->getId(),
                $this->model->getAttribute()->getId(),
                $this->objectManager->get(
                    \Magento\Store\Model\StoreManagerInterface::class
                )->getStore()->getId()
            )
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoConfigFixture current_store catalog/price/scope 2
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testAfterSaveWithDifferentStores()
    {
        $repository = $this->objectManager->create(
            \Magento\Catalog\Model\ProductRepository::class
        );
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->objectManager->create(
            \Magento\Store\Model\Store::class
        );
        $globalStoreId = $store->load('admin')->getId();
        $secondStoreId = $store->load('fixture_second_store')->getId();
        $thirdStoreId = $store->load('fixture_third_store')->getId();
        /** @var \Magento\Catalog\Model\Product\Action $productAction */
        $productAction = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Action::class
        );

        $product = $repository->get('simple');
        $productId = $product->getId();
        $productResource = $product->getResource();
        $productAction->updateWebsites([$productId], [$store->load('fixture_second_store')->getWebsiteId()], 'add');
        $product->setOrigData();
        $product->setStoreId($secondStoreId);
        $product->setPrice(9.99);
        $productResource->save($product);

        $this->assertEquals(
            '10.00',
            $productResource->getAttributeRawValue($productId, $this->model->getAttribute()->getId(), $globalStoreId)
        );
        $this->assertEquals(
            '9.99',
            $productResource->getAttributeRawValue($productId, $this->model->getAttribute()->getId(), $secondStoreId)
        );
        $this->assertEquals(
            '9.99',
            $productResource->getAttributeRawValue($productId, $this->model->getAttribute()->getId(), $thirdStoreId)
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoConfigFixture current_store catalog/price/scope 2
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testAfterSaveWithSameCurrency()
    {
        $repository = $this->objectManager->create(
            \Magento\Catalog\Model\ProductRepository::class
        );
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->objectManager->create(
            \Magento\Store\Model\Store::class
        );
        $globalStoreId = $store->load('admin')->getId();
        $secondStoreId = $store->load('fixture_second_store')->getId();
        $thirdStoreId = $store->load('fixture_third_store')->getId();
        /** @var \Magento\Catalog\Model\Product\Action $productAction */
        $productAction = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Action::class
        );

        $product = $repository->get('simple');
        $productId = $product->getId();
        $productResource = $product->getResource();
        $productAction->updateWebsites([$productId], [$store->load('fixture_second_store')->getWebsiteId()], 'add');
        $product->setOrigData();
        $product->setStoreId($secondStoreId);
        $product->setPrice(9.99);
        $productResource->save($product);

        $this->assertEquals(
            '10.00',
            $productResource->getAttributeRawValue($productId, $this->model->getAttribute()->getId(), $globalStoreId)
        );
        $this->assertEquals(
            '9.99',
            $productResource->getAttributeRawValue($productId, $this->model->getAttribute()->getId(), $secondStoreId)
        );
        $this->assertEquals(
            '9.99',
            $productResource->getAttributeRawValue($productId, $this->model->getAttribute()->getId(), $thirdStoreId)
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoConfigFixture current_store catalog/price/scope 2
     */
    public function testAfterSaveWithUseDefault()
    {
        $repository = $this->objectManager->create(
            \Magento\Catalog\Model\ProductRepository::class
        );
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->objectManager->create(
            \Magento\Store\Model\Store::class
        );
        $globalStoreId = $store->load('admin')->getId();
        $secondStoreId = $store->load('fixture_second_store')->getId();
        $thirdStoreId = $store->load('fixture_third_store')->getId();
        /** @var \Magento\Catalog\Model\Product\Action $productAction */
        $productAction = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Action::class
        );

        $product = $repository->get('simple');
        $productId = $product->getId();
        $productResource = $product->getResource();
        $productAction->updateWebsites([$productId], [$store->load('fixture_second_store')->getWebsiteId()], 'add');
        $product->setOrigData();
        $product->setStoreId($secondStoreId);
        $product->setPrice(9.99);
        $productResource->save($product);

        $this->assertEquals(
            '10.00',
            $productResource->getAttributeRawValue($productId, $this->model->getAttribute()->getId(), $globalStoreId)
        );
        $this->assertEquals(
            '9.99',
            $productResource->getAttributeRawValue($productId, $this->model->getAttribute()->getId(), $secondStoreId)
        );
        $this->assertEquals(
            '9.99',
            $productResource->getAttributeRawValue($productId, $this->model->getAttribute()->getId(), $thirdStoreId)
        );

        $product->setStoreId($thirdStoreId);
        $product->setPrice(null);
        $productResource->save($product);

        $this->assertEquals(
            '10.00',
            $productResource->getAttributeRawValue($productId, $this->model->getAttribute()->getId(), $globalStoreId)
        );
        $this->assertEquals(
            '9.99',
            $productResource->getAttributeRawValue($productId, $this->model->getAttribute()->getId(), $secondStoreId)
        );
        $this->assertEquals(
            '10.00',
            $productResource->getAttributeRawValue($productId, $this->model->getAttribute()->getId(), $thirdStoreId)
        );
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Observer\SwitchPriceAttributeScopeOnConfigChange;
use Magento\Framework\App\Config\ReinitableConfigInterface;

/**
 * Test class for \Magento\Catalog\Model\Product\Attribute\Backend\Price.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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

    /** @var ProductRepositoryInterface */
    private $productRepository;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var ReinitableConfigInterface $reinitiableConfig */
        $reinitiableConfig = $this->objectManager->get(ReinitableConfigInterface::class);
        $reinitiableConfig->setValue(
            'catalog/price/scope',
            \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE
        );
        $observer = $this->objectManager->get(\Magento\Framework\Event\Observer::class);
        $this->objectManager->get(SwitchPriceAttributeScopeOnConfigChange::class)
            ->execute($observer);

        $this->model = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Attribute\Backend\Price::class
        );
        $this->productRepository = $this->objectManager->create(
            ProductRepositoryInterface::class
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
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->objectManager->create(\Magento\Store\Model\Store::class);
        $globalStoreId = $store->load('admin')->getId();
        $product = $this->productRepository->get('simple');
        $product->setPrice('9.99');
        $product->setStoreId($globalStoreId);
        $product->getResource()->save($product);
        $product = $this->productRepository->get('simple', false, $globalStoreId, true);
        $this->assertEquals('9.99', $product->getPrice());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoConfigFixture current_store catalog/price/scope 1
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testAfterSaveWithDifferentStores()
    {
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

        $product = $this->productRepository->get('simple');
        $productId = $product->getId();
        $productAction->updateWebsites([$productId], [$store->load('fixture_second_store')->getWebsiteId()], 'add');
        $product->setStoreId($secondStoreId);
        $product->setPrice('9.99');
        $product->getResource()->save($product);

        $product = $this->productRepository->get('simple', false, $globalStoreId, true);
        $this->assertEquals(10, $product->getPrice());

        $product = $this->productRepository->get('simple', false, $secondStoreId, true);
        $this->assertEquals('9.99', $product->getPrice());

        $product = $this->productRepository->get('simple', false, $thirdStoreId, true);
        $this->assertEquals('9.99', $product->getPrice());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoConfigFixture current_store catalog/price/scope 1
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testAfterSaveWithSameCurrency()
    {
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

        $product = $this->productRepository->get('simple');
        $productId = $product->getId();
        $productAction->updateWebsites([$productId], [$store->load('fixture_second_store')->getWebsiteId()], 'add');
        $product->setOrigData();
        $product->setStoreId($secondStoreId);
        $product->setPrice('9.99');
        $product->getResource()->save($product);

        $product = $this->productRepository->get('simple', false, $globalStoreId, true);
        $this->assertEquals(10, $product->getPrice());

        $product = $this->productRepository->get('simple', false, $secondStoreId, true);
        $this->assertEquals('9.99', $product->getPrice());

        $product = $this->productRepository->get('simple', false, $thirdStoreId, true);
        $this->assertEquals('9.99', $product->getPrice());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoConfigFixture current_store catalog/price/scope 1
     */
    public function testAfterSaveWithUseDefault()
    {
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

        $product = $this->productRepository->get('simple');
        $productId = $product->getId();
        $productAction->updateWebsites([$productId], [$store->load('fixture_second_store')->getWebsiteId()], 'add');
        $product->setOrigData();
        $product->setStoreId($secondStoreId);
        $product->setPrice('9.99');
        $product->getResource()->save($product);

        $product = $this->productRepository->get('simple', false, $globalStoreId, true);
        $this->assertEquals(10, $product->getPrice());

        $product = $this->productRepository->get('simple', false, $secondStoreId, true);
        $this->assertEquals('9.99', $product->getPrice());

        $product = $this->productRepository->get('simple', false, $thirdStoreId, true);
        $this->assertEquals('9.99', $product->getPrice());

        $product->setStoreId($thirdStoreId);
        $product->setPrice(null);
        $product->getResource()->save($product);

        $product = $this->productRepository->get('simple', false, $globalStoreId, true);
        $this->assertEquals(10, $product->getPrice());

        $product = $this->productRepository->get('simple', false, $secondStoreId, true);
        $this->assertEquals(10, $product->getPrice());

        $product = $this->productRepository->get('simple', false, $thirdStoreId, true);
        $this->assertEquals(10, $product->getPrice());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoConfigFixture default_store catalog/price/scope 1
     */
    public function testAfterSaveForWebsitesWithDifferentCurrencies()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->objectManager->create(
            \Magento\Store\Model\Store::class
        );

        /** @var \Magento\Directory\Model\ResourceModel\Currency $rate */
        $rate = $this->objectManager->create(\Magento\Directory\Model\ResourceModel\Currency::class);
        $rate->saveRates([
            'USD' => ['EUR' => 2],
            'EUR' => ['USD' => 0.5]
        ]);

        $globalStoreId = $store->load('admin')->getId();
        $secondStore = $store->load('fixture_second_store');
        $secondStoreId = $store->load('fixture_second_store')->getId();
        $thirdStoreId = $store->load('fixture_third_store')->getId();

        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = $this->objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $config->setValue(
            'currency/options/default',
            'EUR',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
            'test'
        );

        $productAction = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Action::class
        );
        $product = $this->productRepository->get('simple');
        $productId = $product->getId();
        $productAction->updateWebsites([$productId], [$secondStore->getWebsiteId()], 'add');
        $product->setOrigData();
        $product->setStoreId($globalStoreId);
        $product->setPrice(100);
        $product->getResource()->save($product);

        $product = $this->productRepository->get('simple', false, $globalStoreId, true);
        $this->assertEquals(100, $product->getPrice());

        $product = $this->productRepository->get('simple', false, $secondStoreId, true);
        $this->assertEquals(100, $product->getPrice());

        $product = $this->productRepository->get('simple', false, $thirdStoreId, true);
        $this->assertEquals(100, $product->getPrice());
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        /** @var ReinitableConfigInterface $reinitiableConfig */
        $reinitiableConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            ReinitableConfigInterface::class
        );
        $reinitiableConfig->setValue(
            'catalog/price/scope',
            \Magento\Store\Model\Store::PRICE_SCOPE_GLOBAL
        );
        $observer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Event\Observer::class
        );
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(SwitchPriceAttributeScopeOnConfigChange::class)
            ->execute($observer);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Locator;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test registry locator
 *
 * @see \Magento\Catalog\Model\Locator\RegistryLocator
 * @magentoAppArea frontend
 */
class RegistryLocatorTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var RegistryLocator */
    private $registryLocator;

    /** @var Registry */
    private $registry;

    /** @var ProductRepositoryInterface */
    private $productRepository;


    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->registryLocator = $this->objectManager->get(RegistryLocator::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('current_product');
        $this->registry->unregister('current_store');

        parent::tearDown();
    }


    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/product_two_websites.php
     *
     * @return void
     */
    public function testGetWebsiteIds(): void
    {
        $product = $this->productRepository->get('simple-on-two-websites');
        $this->registerProduct($product);
        $this->assertEquals($product->getExtensionAttributes()->getWebsiteIds(), $this->registryLocator->getWebsiteIds());
    }

    /**
     * @return void
     */
    public function testGetBaseCurrencyCode(): void
    {
        $store = $this->storeManager->getStore();
        $this->registerStore($store);
        $this->assertEquals($store->getBaseCurrencyCode(), $this->registryLocator->getBaseCurrencyCode());
    }

    /**
     * Register the product
     *
     * @param ProductInterface $product
     * @return void
     */
    private function registerProduct(ProductInterface $product): void
    {
        $this->registry->unregister('current_product');
        $this->registry->register('current_product', $product);
    }

    /**
     * Register the store
     *
     * @param StoreInterface $store
     * @return void
     */
    private function registerStore(StoreInterface $store): void
    {
        $this->registry->unregister('current_store');
        $this->registry->register('current_store', $store);
    }
}

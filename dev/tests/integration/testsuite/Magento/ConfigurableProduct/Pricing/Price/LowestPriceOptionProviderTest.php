<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

class LowestPriceOptionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LowestPriceOptionsProviderInterface
     */
    private $lowestPriceOptionsProvider;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->lowestPriceOptionsProvider = Bootstrap::getObjectManager()->get(
            LowestPriceOptionsProviderInterface::class
        );
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetProductsIfOneOfChildIsDisabled()
    {
        $configurableProduct = $this->productRepository->get('configurable', false, null, true);
        $lowestPriceChildrenProducts = $this->lowestPriceOptionsProvider->getProducts($configurableProduct);
        $this->assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        $this->assertEquals(10, $lowestPriceChildrenProduct->getPrice());

        // load full aggregation root
        $lowestPriceChildProduct = $this->productRepository->get(
            $lowestPriceChildrenProduct->getSku(),
            false,
            null,
            true
        );
        $lowestPriceChildProduct->setStatus(Status::STATUS_DISABLED);
        // update in global scope
        $currentStoreId = $this->storeManager->getStore()->getId();
        $this->storeManager->setCurrentStore(Store::ADMIN_CODE);
        $this->productRepository->save($lowestPriceChildProduct);
        $this->storeManager->setCurrentStore($currentStoreId);

        $lowestPriceChildrenProducts = $this->lowestPriceOptionsProvider->getProducts($configurableProduct);
        $this->assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        $this->assertEquals(20, $lowestPriceChildrenProduct->getPrice());
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetProductsIfOneOfChildIsDisabledPerStore()
    {
        $configurableProduct = $this->productRepository->getById(1, false, null, true);
        $lowestPriceChildrenProducts = $this->lowestPriceOptionsProvider->getProducts($configurableProduct);
        $this->assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        $this->assertEquals(10, $lowestPriceChildrenProduct->getPrice());

        // load full aggregation root
        $lowestPriceChildProduct = $this->productRepository->get(
            $lowestPriceChildrenProduct->getSku(),
            false,
            null,
            true
        );
        $lowestPriceChildProduct->setStatus(Status::STATUS_DISABLED);
        // update in default store scope
        $currentStoreId = $this->storeManager->getStore()->getId();
        $defaultStore = $this->storeManager->getDefaultStoreView();
        $this->storeManager->setCurrentStore($defaultStore->getId());
        $this->productRepository->save($lowestPriceChildProduct);
        $this->storeManager->setCurrentStore($currentStoreId);

        $lowestPriceChildrenProducts = $this->lowestPriceOptionsProvider->getProducts($configurableProduct);
        $this->assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        $this->assertEquals(20, $lowestPriceChildrenProduct->getPrice());
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetProductsIfOneOfChildIsOutOfStock()
    {
        $configurableProduct = $this->productRepository->get('configurable', false, null, true);
        $lowestPriceChildrenProducts = $this->lowestPriceOptionsProvider->getProducts($configurableProduct);
        $this->assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        $this->assertEquals(10, $lowestPriceChildrenProduct->getPrice());

        // load full aggregation root
        $lowestPriceChildProduct = $this->productRepository->get(
            $lowestPriceChildrenProduct->getSku(),
            false,
            null,
            true
        );
        $stockItem = $lowestPriceChildProduct->getExtensionAttributes()->getStockItem();
        $stockItem->setIsInStock(0);
        $this->productRepository->save($lowestPriceChildProduct);

        $lowestPriceChildrenProducts = $this->lowestPriceOptionsProvider->getProducts($configurableProduct);
        $this->assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        $this->assertEquals(20, $lowestPriceChildrenProduct->getPrice());
    }
}

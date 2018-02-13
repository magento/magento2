<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

class LowestPriceOptionProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetProductsIfOneOfChildIsDisabled()
    {
        $configurableProduct = $this->productRepository->get('configurable', false, null, true);
        $lowestPriceChildrenProducts = $this->createLowestPriceOptionsProvider()->getProducts($configurableProduct);
        self::assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        self::assertEquals(10, $lowestPriceChildrenProduct->getPrice());

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

        $lowestPriceChildrenProducts = $this->createLowestPriceOptionsProvider()->getProducts($configurableProduct);
        self::assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        self::assertEquals(20, $lowestPriceChildrenProduct->getPrice());
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetProductsIfOneOfChildIsDisabledPerStore()
    {
        $configurableProduct = $this->productRepository->get('configurable', false, null, true);
        $lowestPriceChildrenProducts = $this->createLowestPriceOptionsProvider()->getProducts($configurableProduct);
        self::assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        self::assertEquals(10, $lowestPriceChildrenProduct->getPrice());

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

        $lowestPriceChildrenProducts = $this->createLowestPriceOptionsProvider()->getProducts($configurableProduct);
        self::assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        self::assertEquals(20, $lowestPriceChildrenProduct->getPrice());
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetProductsIfOneOfChildIsOutOfStock()
    {
        $configurableProduct = $this->productRepository->get('configurable', false, null, true);
        $lowestPriceChildrenProducts = $this->createLowestPriceOptionsProvider()->getProducts($configurableProduct);
        self::assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        self::assertEquals(10, $lowestPriceChildrenProduct->getPrice());

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
        $lowestPriceChildrenProducts = $this->createLowestPriceOptionsProvider()->getProducts($configurableProduct);
        self::assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        self::assertEquals(20, $lowestPriceChildrenProduct->getPrice());
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDataFixture Magento/Store/_files/website.php
     */
    public function testGetProductsIfOneOfChildrenIsAssignedToOtherWebsite()
    {
        $configurableProduct = $this->productRepository->getById(1, false, null, true);
        $lowestPriceChildrenProducts = $this->createLowestPriceOptionsProvider()->getProducts($configurableProduct);
        self::assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        self::assertEquals(10, $lowestPriceChildrenProduct->getPrice());

        /** @var \Magento\Store\Api\WebsiteRepositoryInterface $webSiteRepository */
        $webSiteRepository = Bootstrap::getObjectManager()->get(\Magento\Store\Api\WebsiteRepositoryInterface::class);
        $website = $webSiteRepository->get('test');

        $attributes = $lowestPriceChildrenProduct->getExtensionAttributes();
        $attributes->setWebsiteIds([$website->getId()]);

        $lowestPriceChildrenProduct->setExtensionAttributes($attributes);
        $this->productRepository->save($lowestPriceChildrenProduct);

        $lowestPriceChildrenProducts = $this->createLowestPriceOptionsProvider()->getProducts($configurableProduct);
        self::assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        self::assertEquals(20, $lowestPriceChildrenProduct->getPrice());
    }

    /**
     * As LowestPriceOptionsProviderInterface used multiple times in scope
     * of one test we need to always recreate it and prevent internal caching in property
     * @return LowestPriceOptionsProviderInterface
     */
    private function createLowestPriceOptionsProvider()
    {
        return Bootstrap::getObjectManager()->create(
            LowestPriceOptionsProviderInterface::class
        );
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test Product Custom Options
 *
 * @magentoAppArea adminhtml
 */
class OptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
    }

    /**
     * Test to save option price by store
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_options.php
     * @magentoDataFixture Magento/Store/_files/core_second_third_fixturestore.php
     * @magentoConfigFixture default_store catalog/price/scope 1
     * @magentoConfigFixture secondstore_store catalog/price/scope 1
     */
    public function testSaveOptionPriceByStore()
    {
        $secondWebsitePrice = 22.0;
        $defaultStoreId = $this->storeManager->getStore()->getId();
        $secondStoreId = $this->storeManager->getStore('secondstore')->getId();

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');
        $option = $product->getOptions()[0];
        $defaultPrice = $option->getPrice();

        $option->setPrice($secondWebsitePrice);
        $product->setStoreId($secondStoreId);
        // set Current store='secondstore' to correctly save product options for 'secondstore'
        $this->storeManager->setCurrentStore($secondStoreId);
        $this->productRepository->save($product);
        $this->storeManager->setCurrentStore($defaultStoreId);

        $product = $this->productRepository->get('simple', false, Store::DEFAULT_STORE_ID, true);
        $option = $product->getOptions()[0];
        $this->assertEquals($defaultPrice, $option->getPrice(), 'Price value by default store is wrong');

        $product = $this->productRepository->get('simple', false, $secondStoreId, true);
        $option = $product->getOptions()[0];
        $this->assertEquals($secondWebsitePrice, $option->getPrice(), 'Price value by not default store is wrong');
    }
}

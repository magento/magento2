<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductTierPriceInterface;
use Magento\TestFramework\Helper\Bootstrap;

class FinalPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     * @magentoAppIsolation enabled
     */
    public function testFinalPrice()
    {
        $productRepository = Bootstrap::getObjectManager()
            ->get('\Magento\Catalog\Api\ProductRepositoryInterface');
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $productRepository->get('grouped-product');

        $this->assertEquals(10, $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue());
    }

    /**
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     * @magentoAppIsolation enabled
     */
    public function testFinalPriceWithTearPrice()
    {
        $productRepository = Bootstrap::getObjectManager()
            ->get('\Magento\Catalog\Api\ProductRepositoryInterface');
        /** @var ProductTierPriceInterface $tierPrice */
        $tierPrice = Bootstrap::getObjectManager()->create(ProductTierPriceInterface::class);
        $tierPrice->setQty(1);
        $tierPrice->setCustomerGroupId(\Magento\Customer\Model\GroupManagement::CUST_GROUP_ALL);
        $tierPrice->setValue(5);

        /** @var $simpleProduct \Magento\Catalog\Api\Data\ProductInterface */
        $simpleProduct = $productRepository->get('simple');
        $simpleProduct->setTierPrices([
            $tierPrice
        ]);
        $productRepository->save($simpleProduct);

        /** @var $product \Magento\Catalog\Model\Product */
        $product = $productRepository->get('grouped-product');
        $this->assertEquals(5, $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue());
    }

    /**
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     * @magentoAppIsolation enabled
     */
    public function testFinalPriceWithSpecialPrice()
    {
        $productRepository = Bootstrap::getObjectManager()
            ->get('\Magento\Catalog\Api\ProductRepositoryInterface');

        /** @var $simpleProduct \Magento\Catalog\Api\Data\ProductInterface */
        $simpleProduct = $productRepository->get('simple');
        $simpleProduct->setCustomAttribute('special_price', 6);
        $productRepository->save($simpleProduct);

        /** @var $product \Magento\Catalog\Model\Product */
        $product = $productRepository->get('grouped-product');
        $this->assertEquals(6, $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue());
    }
}

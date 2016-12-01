<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class FinalPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testNullPriceForConfigurbaleIfAllChildIsOutOfStock()
    {
        //prepare configurable product
        $configurableProduct = $this->productRepository->get('configurable', false, null, true);
        foreach ($configurableProduct->getExtensionAttributes()->getConfigurableProductLinks() as $productId) {
            $product = $this->productRepository->getById($productId);
            $product->getExtensionAttributes()->getStockItem()->setIsInStock(0);
            $this->productRepository->save($product);
        }

        $finalPrice = Bootstrap::getObjectManager()->create(
            FinalPrice::class,
            [
                'saleableItem' => $configurableProduct,
                'quantity' => 1
            ]
        );

        static::assertNull($finalPrice->getValue());
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testNullPriceForConfigurbaleIfAllChildIsDisabled()
    {
        //prepare configurable product
        $configurableProduct = $this->productRepository->get('configurable', false, null, true);
        foreach ($configurableProduct->getExtensionAttributes()->getConfigurableProductLinks() as $productId) {
            $product = $this->productRepository->getById($productId);
            $product->setStatus(Status::STATUS_DISABLED);
            $this->productRepository->save($product);
        }

        $finalPrice = Bootstrap::getObjectManager()->create(
            FinalPrice::class,
            [
                'saleableItem' => $configurableProduct,
                'quantity' => 1
            ]
        );

        static::assertNull($finalPrice->getValue());
    }
}

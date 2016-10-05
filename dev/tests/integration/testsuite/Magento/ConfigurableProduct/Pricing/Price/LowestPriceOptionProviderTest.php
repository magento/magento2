<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppIsolation enabled
 */
class LowestPriceOptionProviderTest extends \PHPUnit_Framework_TestCase
{
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
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->lowestPriceOptionsProvider = Bootstrap::getObjectManager()->get(
            LowestPriceOptionsProviderInterface::class
        );
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_two_simple.php
     */
    public function testGetProductsIfOneOfChildIsDisabled()
    {
        $configurableProduct = $this->productRepository->get('configurable_product_with_two_simple');
        $lowestPriceChildrenProducts = $this->lowestPriceOptionsProvider->getProducts($configurableProduct);
        $this->assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        $this->assertEquals(10, $lowestPriceChildrenProduct->getPrice());

        // load full aggregation root
        $lowestPriceChildProduct = $this->productRepository->get($lowestPriceChildrenProduct->getSku());
        $lowestPriceChildProduct->setStatus(Status::STATUS_DISABLED);
        $this->productRepository->save($lowestPriceChildProduct);

        $lowestPriceChildrenProducts = $this->lowestPriceOptionsProvider->getProducts($configurableProduct);
        $this->assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        $this->assertEquals(20, $lowestPriceChildrenProduct->getPrice());
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_two_simple.php
     */
    public function testGetProductsIfOneOfChildIsOutOfStock()
    {
        $configurableProduct = $this->productRepository->get('configurable_product_with_two_simple');
        $lowestPriceChildrenProducts = $this->lowestPriceOptionsProvider->getProducts($configurableProduct);
        $this->assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        $this->assertEquals(10, $lowestPriceChildrenProduct->getPrice());

        // load full aggregation root
        $lowestPriceChildProduct = $this->productRepository->get($lowestPriceChildrenProduct->getSku());
        $stockItem = $lowestPriceChildProduct->getExtensionAttributes()->getStockItem();
        $stockItem->setIsInStock(false);
        // TODO: Need to delete next string after MAGETWO-59315 fixing
        $lowestPriceChildProduct->setStockData(['is_in_stock' => 0, 'qty' => 0]);
        $this->productRepository->save($lowestPriceChildProduct);

        $lowestPriceChildrenProducts = $this->lowestPriceOptionsProvider->getProducts($configurableProduct);
        $this->assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        $this->assertEquals(20, $lowestPriceChildrenProduct->getPrice());
    }
}

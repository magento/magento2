<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Product\View\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\Status;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable.
 *
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
 */
class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testGetAllowAttributes()
    {
        $attributes = $this->getSubject()->getAllowAttributes();
        $this->assertInstanceOf(
            'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection',
            $attributes
        );
        $this->assertGreaterThanOrEqual(1, $attributes->getSize());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testHasOptions()
    {
        $this->assertTrue($this->getSubject()->hasOptions());
    }

    /**
     * @magentoAppIsolation enabled
     * @dataProvider allowProductsDataProvider
     */
    public function testGetAllowProducts($isInStock, $status, $expectedCount)
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $childProduct = $productRepository->get('simple_10');
        $childProduct->setStatus($status);
        $stockItem = $childProduct->getExtensionAttributes()->getStockItem();
        $stockItem->setIsInStock($isInStock);
        $productRepository->save($childProduct);

        $products = $this->getSubject()->getAllowProducts();
        $this->assertCount($expectedCount, $products);
        foreach ($products as $product) {
            $this->assertInstanceOf('Magento\Catalog\Model\Product', $product);
        }
    }

    /**
     * @return array
     */
    public function allowProductsDataProvider()
    {
        return [
            [Status::STATUS_OUT_OF_STOCK, false, 1],
            [Status::STATUS_OUT_OF_STOCK, true, 1],
            [Status::STATUS_IN_STOCK, false, 1],
            [Status::STATUS_IN_STOCK, true, 2],
        ];
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetJsonConfig()
    {
        $config = json_decode($this->getSubject()->getJsonConfig(), true);
        $this->assertNotEmpty($config);
        $this->assertArrayHasKey('productId', $config);
        $this->assertEquals(1001, $config['productId']);
        $this->assertArrayHasKey('attributes', $config);
        $this->assertArrayHasKey('template', $config);
        $this->assertArrayHasKey('prices', $config);
        $this->assertArrayHasKey('basePrice', $config['prices']);
    }

    /**
     * @return Configurable
     */
    private function getSubject()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()
            ->create(ProductRepositoryInterface::class);
        $product = $productRepository->get('configurable');
        /** @var \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $block */
        $block = Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\ConfigurableProduct\Block\Product\View\Type\Configurable'
        );
        $block->setProduct($product);
        return $block;
    }
}

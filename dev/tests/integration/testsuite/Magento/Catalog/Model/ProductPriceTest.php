<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Api\StockRegistryInterface;

/**
 * Tests product model:
 * - pricing behaviour is tested
 *
 * @see \Magento\Catalog\Model\ProductTest
 * @see \Magento\Catalog\Model\ProductExternalTest
 */
class ProductPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_model;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_model = Bootstrap::getObjectManager()->create(Product::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testGetPrice()
    {
        $this->assertEmpty($this->_model->getPrice());
        $this->_model->setPrice(10.0);
        $this->assertEquals(10.0, $this->_model->getPrice());
    }

    /**
     * @return void
     */
    public function testGetPriceModel()
    {
        $default = $this->_model->getPriceModel();
        $this->assertInstanceOf(\Magento\Catalog\Model\Product\Type\Price::class, $default);
        $this->assertSame($default, $this->_model->getPriceModel());
    }

    /**
     * See detailed tests at \Magento\Catalog\Model\Product\Type*_PriceTest
     */
    public function testGetTierPrice()
    {
        $this->assertEquals([], $this->_model->getTierPrice());
    }

    /**
     * See detailed tests at \Magento\Catalog\Model\Product\Type*_PriceTest
     */
    public function testGetTierPriceCount()
    {
        $this->assertEquals(0, $this->_model->getTierPriceCount());
    }

    /**
     * See detailed tests at \Magento\Catalog\Model\Product\Type*_PriceTest
     */
    public function testGetFormatedPrice()
    {
        $this->assertEquals('<span class="price">$0.00</span>', $this->_model->getFormatedPrice());
    }

    /**
     * @return void
     */
    public function testSetGetFinalPrice()
    {
        $this->assertEquals(0, $this->_model->getFinalPrice());
        $this->_model->setPrice(10);
        $this->_model->setFinalPrice(10);
        $this->assertEquals(10, $this->_model->getFinalPrice());
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/product_with_options.php
     * @return void
     */
    public function testGetMinPrice(): void
    {
        $product = $this->productRepository->get('simple');
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        $collection->addIdFilter($product->getId());
        $collection->addPriceData();
        $collection->load();
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $collection->getFirstItem();
        $this->assertEquals(323, $product->getData('min_price'));
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable_sku.php
     */
    public function testGetMinPriceForComposite(): void
    {
        $confProduct = $this->productRepository->get('configurable');
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        $collection->addIdFilter($confProduct->getId());
        $collection->addPriceData();
        $collection->load();
        $product = $collection->getFirstItem();
        $this->assertEquals(10, $product->getData('min_price'));

        $childProduct = $this->productRepository->get('simple_10');
        $stockRegistry = Bootstrap::getObjectManager()->get(StockRegistryInterface::class);
        $stockItem = $stockRegistry->getStockItem($childProduct->getId());
        $stockItem->setIsInStock(false);
        $stockRegistry->updateStockItemBySku($childProduct->getSku(), $stockItem);
        $collection->clear()->load();
        $product = $collection->getFirstItem();
        $this->assertEquals(20, $product->getData('min_price'));
    }
}

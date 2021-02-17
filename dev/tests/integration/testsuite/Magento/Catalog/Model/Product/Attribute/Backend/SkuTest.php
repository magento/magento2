<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Copier;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Catalog\Model\Product\Attribute\Backend\Sku.
 * @magentoAppArea adminhtml
 */
class SkuTest extends TestCase
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGenerateUniqueSkuExistingProduct(): void
    {
        $repository = Bootstrap::getObjectManager()->create(ProductRepository::class);
        $product = $repository->get('simple');
        $product->setId(null);
        $this->assertEquals('simple', $product->getSku());
        $product->getResource()->getAttribute('sku')->getBackend()->beforeSave($product);
        $this->assertEquals('simple-1', $product->getSku());
    }

    /**
     * @param $product Product
     * @dataProvider uniqueSkuDataProvider
     */
    public function testGenerateUniqueSkuNotExistingProduct(Product $product): void
    {
        $this->assertEquals('simple', $product->getSku());
        $product->getResource()->getAttribute('sku')->getBackend()->beforeSave($product);
        $this->assertEquals('simple', $product->getSku());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     */
    public function testGenerateUniqueLongSku(): void
    {
        $repository = Bootstrap::getObjectManager()->create(ProductRepository::class);
        $productFactory = Bootstrap::getObjectManager()->create(ProductFactory::class);
        $product = $repository->get('simple');
        $product->setSku('0123456789012345678901234567890123456789012345678901234567890123');

        /** @var Copier $copier */
        $copier = Bootstrap::getObjectManager()->get(Copier::class);
        $copier->copy($product, $productFactory->create());
        $this->assertEquals('0123456789012345678901234567890123456789012345678901234567890123', $product->getSku());
        $product->getResource()->getAttribute('sku')->getBackend()->beforeSave($product);
        $this->assertEquals('01234567890123456789012345678901234567890123456789012345678901-1', $product->getSku());
    }

    /**
     * Returns simple product
     *
     * @return array
     */
    public function uniqueSkuDataProvider(): array
    {
        $product = $this->getProduct();
        return [[$product]];
    }

    /**
     * Get product form data provider
     *
     * @return Product
     */
    private function getProduct(): Product
    {
        /** @var $product Product */
        $product = Bootstrap::getObjectManager()->create(Product::class);
        $product->setTypeId(Type::TYPE_SIMPLE)
            ->setId(1)
            ->setAttributeSetId(4)
            ->setWebsiteIds([1])
            ->setName('Simple Product')
            ->setSku('simple')
            ->setPrice(10)
            ->setDescription('Description with <b>html tag</b>')
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED)
            ->setCategoryIds([2])
            ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);

        return $product;
    }
}

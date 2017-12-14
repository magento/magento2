<?php
/**
 * Copyright :copyright: Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Integration\Locator;

use Magento\Catalog\Model\ProductIdLocator;
use Magento\Catalog\Model\ProductSkuLocator;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;
use PHPUnit\Framework\TestCase;

class GetProductSkuByProductIdLocatorTest extends TestCase
{
    /**
     * @var ProductSkuLocator
     */
    private $productSkuLocator;

    /**
     * @var ProductIdLocator
     */
    private $productIdLocator;

    /**
     * @var
     */
    private $productRepository;

    /**
     * @var array
     */
    private $productSkus = ['SKU-1', 'SKU-2', 'SKU-3'];

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->productSkuLocator = Bootstrap::getObjectManager()->get(ProductSkuLocator::class);
        $this->productIdLocator = Bootstrap::getObjectManager()->get(ProductIdLocator::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     */
    public function testGetSkuByProductId()
    {
        $expectedProductSkus = $this->getProductSkusToCompare();
        $executeResult = $this->productSkuLocator->retrieveSkusByProductIds(array_flip($expectedProductSkus));

        self::assertEquals($expectedProductSkus, $executeResult);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     */
    public function testGetProductIdBySku()
    {
        $expectedProductIds = $this->getProductIdsToCompare();
        $executeResult = $this->productIdLocator->retrieveProductIdsBySkus($this->productSkus);

        self::assertEquals($executeResult, $expectedProductIds);
    }

    /**
     * @return array
     */
    private function getProductSkusToCompare()
    {
        $productsSkus = [];
        foreach ($this->productSkus as $sku)  {
            $product = $this->productRepository->get($sku);
            $productsSkus[$product->getId()] = $sku;
        }

        return $productsSkus;
    }

    /**
     * @return array
     */
    private function getProductIdsToCompare()
    {
        $productsIds = [];
        foreach ($this->productSkus as $sku)  {
            $product = $this->productRepository->get($sku);
            $productsIds[$sku] = [$product->getId() => 'simple'];
        }

        return $productsIds;
    }
}
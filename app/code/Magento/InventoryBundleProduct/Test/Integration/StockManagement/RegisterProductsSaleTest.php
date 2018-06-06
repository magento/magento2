<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleProduct\Test\Integration\StockManagement;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\StockManagement;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class RegisterProductsSaleTest extends TestCase
{
    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var StockManagement
     */
    private $stockManagement;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->getProductSalableQty = Bootstrap::getObjectManager()->get(GetProductSalableQtyInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->websiteRepository = Bootstrap::getObjectManager()->get(WebsiteRepositoryInterface::class);
        $this->stockManagement = Bootstrap::getObjectManager()->get(StockManagement::class);
        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
        $this->isProductSalable = Bootstrap::getObjectManager()->get(IsProductSalableInterface::class);
    }

    /**
     * Test reservations will not be created and qty won't be subtracted during registration product sale
     * for bundle product.
     *
     * @magentoDataFixture ../../../../dev/tests/integration/testsuite/Magento/Bundle/_files/product.php
     * @return void
     */
    public function testRegisterProductsSale()
    {
        self::assertEquals(
            22,
            $this->getProductSalableQty->execute('simple', $this->defaultStockProvider->getId())
        );
        self::assertEquals(
            24,
            $this->getProductSalableQty->execute('custom-design-simple-product', $this->defaultStockProvider->getId())
        );
        $bundleProduct = $this->productRepository->get('bundle-product');
        $bundleOptionProduct1 = $this->productRepository->get('simple');
        $bundleOptionProduct2 = $this->productRepository->get('custom-design-simple-product');
        $website = $this->websiteRepository->get('base');
        $this->stockManagement->registerProductsSale(
            [
                $bundleProduct->getId() => 1,
                $bundleOptionProduct1->getId() => 10,
                $bundleOptionProduct2->getId() => 10,

            ],
            $website->getId()
        );
        self::assertEquals(
            22,
            $this->getProductSalableQty->execute('simple', $this->defaultStockProvider->getId())
        );
        self::assertEquals(
            24,
            $this->getProductSalableQty->execute('custom-design-simple-product', $this->defaultStockProvider->getId())
        );
    }
}

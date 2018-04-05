<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundle\Test\Integration\StockManagement;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\StockManagement;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventoryReservations\Model\CleanupReservationsInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ReservationPlacingDuringRegisterProductsSaleTest extends TestCase
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
     * @var CleanupReservationsInterface
     */
    private $cleanupReservations;

    /**
     * @var StockManagement
     */
    private $stockManagement;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->getProductSalableQty = Bootstrap::getObjectManager()->get(GetProductSalableQtyInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->websiteRepository = Bootstrap::getObjectManager()->get(WebsiteRepositoryInterface::class);
        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->stockManagement = Bootstrap::getObjectManager()->get(StockManagement::class);
        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
    }

    /**
     * We broke transaction during indexation so we need to clean db state manually
     */
    protected function tearDown()
    {
        $this->cleanupReservations->execute();
    }

    /**
     * Test reservations will be created only for bundle product options, but not for bundle itself.
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
        //Currently IsProductSalableInterface doesn't support complex product types.
        self::assertTrue($bundleProduct->getIsSalable());
        self::assertEquals(0, $bundleProduct->getQty());
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
        $bundleProduct = $this->productRepository->get('bundle-product', false, null, true);
        //Currently IsProductSalableInterface doesn't support complex product types.
        self::assertTrue($bundleProduct->getIsSalable());
        self::assertEquals(0, $bundleProduct->getQty());
        self::assertEquals(
            12,
            $this->getProductSalableQty->execute('simple', $this->defaultStockProvider->getId())
        );
        self::assertEquals(
            14,
            $this->getProductSalableQty->execute('custom-design-simple-product', $this->defaultStockProvider->getId())
        );
    }
}

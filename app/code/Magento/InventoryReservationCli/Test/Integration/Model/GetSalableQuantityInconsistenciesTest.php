<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Test\Integration\Model;

use Magento\InventoryReservationCli\Model\GetSalableQuantityInconsistencies;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @see https://app.hiptest.com/projects/69435/test-plan/folders/909285/scenarios/3026032
 * @see https://app.hiptest.com/projects/69435/test-plan/folders/888618/scenarios/3027875
 * @see https://app.hiptest.com/projects/69435/test-plan/folders/888618/scenarios/3027429
 * @see https://app.hiptest.com/projects/69435/test-plan/folders/888618/scenarios/3027919
 * @see https://app.hiptest.com/projects/69435/test-plan/folders/909285/scenarios/3027919
 * @see https://app.hiptest.com/projects/69435/test-plan/folders/909285/scenarios/3031256
 * @see https://app.hiptest.com/projects/69435/test-plan/folders/909285/scenarios/3031505
 * @see https://app.hiptest.com/projects/69435/test-plan/folders/888618/scenarios/3031591
 * @see https://app.hiptest.com/projects/69435/test-plan/folders/888618/scenarios/3031728
 */
class GetSalableQuantityInconsistenciesTest extends TestCase
{
    /**
     * @var GetSalableQuantityInconsistencies
     */
    private $getSalableQuantityInconsistencies;

    /**
     * Initialize test dependencies
     */
    protected function setUp()
    {
        $this->getSalableQuantityInconsistencies
            = Bootstrap::getObjectManager()->get(GetSalableQuantityInconsistencies::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservationCli/Test/Integration/_files/create_incomplete_order_with_reservation.php
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function testIncompleteOrderWithExistingReservation(): void
    {
        $inconsistencies = $this->getSalableQuantityInconsistencies->execute();
        self::assertSame([], $inconsistencies);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservationCli/Test/Integration/_files/create_incomplete_order_without_reservation.php
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function testIncompleteOrderWithoutReservation(): void
    {
        $inconsistencies = $this->getSalableQuantityInconsistencies->execute();
        self::assertCount(1, $inconsistencies);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservationCli/Test/Integration/_files/order_with_reservation.php
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function testCompletedOrderWithReservations(): void
    {
        $inconsistencies = $this->getSalableQuantityInconsistencies->execute();
        self::assertSame([], $inconsistencies);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_shipping_and_invoice.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservationCli/Test/Integration/_files/broken_reservation.php
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function testCompletedOrderWithMissingReservations(): void
    {
        $inconsistencies = $this->getSalableQuantityInconsistencies->execute();
        self::assertCount(1, $inconsistencies);
    }

    /**
     * Verify inventory:reservations:list-inconsistencies will return correct qty for configurable product.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/create_quote_on_us_website.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/order_configurable_product.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservationCli/Test/Integration/_files/delete_reservations.php
     * @magentoDbIsolation disabled
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/909285/scenarios/3528989
     * @return void
     */
    public function testFindMissingReservationConfigurableProductCustomStock(): void
    {
        $inconsistencies = $this->getSalableQuantityInconsistencies->execute();
        $items = reset($inconsistencies)->getItems();
        self::assertEquals(3, $items['simple_10']);
    }
}

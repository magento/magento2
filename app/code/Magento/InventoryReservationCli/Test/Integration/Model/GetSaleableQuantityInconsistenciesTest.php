<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Test\Integration\Model;

use Magento\InventoryReservationCli\Model\GetSaleableQuantityInconsistencies;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class GetSaleableQuantityInconsistenciesTest extends TestCase
{
    /**
     * @var GetSaleableQuantityInconsistencies
     */
    private $getSaleableQuantityInconsistencies;

    /**
     * Initialize test dependencies
     */
    protected function setUp()
    {
        $this->getSaleableQuantityInconsistencies
            = Bootstrap::getObjectManager()->get(GetSaleableQuantityInconsistencies::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservationCli/Test/Integration/_fixtures/create_incomplete_order_with_reservation.php
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function testIncompleteOrderWithExistingReservation(): void
    {
        $inconsistencies = $this->getSaleableQuantityInconsistencies->execute();
        self::assertSame([], $inconsistencies);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservationCli/Test/Integration/_fixtures/create_incomplete_order_without_reservation.php
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function testIncompleteOrderWithoutReservation(): void
    {
        $inconsistencies = $this->getSaleableQuantityInconsistencies->execute();
        self::assertCount(1, $inconsistencies);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservationCli/Test/Integration/_fixtures/order_with_reservation.php
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function testCompletedOrderWithReservations(): void
    {
        $inconsistencies = $this->getSaleableQuantityInconsistencies->execute();
        self::assertSame([], $inconsistencies);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_shipping_and_invoice.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservationCli/Test/Integration/_fixtures/broken_reservation.php
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function testCompletedOrderWithMissingReservations(): void
    {
        $inconsistencies = $this->getSaleableQuantityInconsistencies->execute();
        self::assertCount(1, $inconsistencies);
    }
}

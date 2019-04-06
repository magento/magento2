<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Test\Integration\Model;

use Magento\InventoryReservations\Model\GetOrderWithBrokenReservation;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class GetListReservationsTotOrdersTest extends TestCase
{

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservations/Test/Integration/_fixtures/order_with_reservation.php
     */
    public function testShouldNotFindAnyInconsistency(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var GetOrderWithBrokenReservation $subject */
        $subject = $objectManager->get(GetOrderWithBrokenReservation::class);

        /** @var array $result */
        $result = $subject->execute();

        self::assertSame([], $result);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_shipping_and_invoice.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservations/Test/Integration/_fixtures/broken_reservation.php
     * @magentoDbIsolation enabled
     */
    public function testShouldReturnOneReservationInconsistency(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var GetOrderWithBrokenReservation $subject */
        $subject = $objectManager->get(GetOrderWithBrokenReservation::class);

        /** @var array $result */
        $result = $subject->execute();

        self::assertCount(1, $result);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Test\Integration\Model;

use Magento\InventoryReservationCli\Model\GetOrderInFinalState;
use Magento\InventoryReservationCli\Model\GetOrderWithBrokenReservation;
use Magento\Sales\Api\Data\OrderInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class GetListReservationsTotOrdersTest extends TestCase
{

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservationCli/Test/Integration/_fixtures/order_with_reservation.php
     */
    public function testShouldNotFindAnyInconsistency(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var GetOrderWithBrokenReservation $getOrderWithBrokenReservation */
        $getOrderWithBrokenReservation = $objectManager->get(GetOrderWithBrokenReservation::class);

        /** @var GetOrderInFinalState $getOrderInFinalState */
        $getOrderInFinalState = $objectManager->get(GetOrderInFinalState::class);

        /** @var array $result */
        $result = $getOrderWithBrokenReservation->execute();

        /** @var OrderInterface[] $orders */
        $orders = $getOrderInFinalState->execute(array_keys($result));

        self::assertSame([], $orders);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_shipping_and_invoice.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservationCli/Test/Integration/_fixtures/broken_reservation.php
     * @magentoDbIsolation enabled
     */
    public function testShouldReturnOneReservationInconsistency(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var GetOrderWithBrokenReservation $getOrderWithBrokenReservation */
        $getOrderWithBrokenReservation = $objectManager->get(GetOrderWithBrokenReservation::class);

        /** @var GetOrderInFinalState $getOrderInFinalState */
        $getOrderInFinalState = $objectManager->get(GetOrderInFinalState::class);

        /** @var array $result */
        $result = $getOrderWithBrokenReservation->execute();

        /** @var OrderInterface[] $orders */
        $orders = $getOrderInFinalState->execute(array_keys($result));

        self::assertCount(1, $orders);
    }
}

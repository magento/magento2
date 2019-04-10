<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Test\Integration\Model;

use Magento\InventoryReservationCli\Model\GetOrdersInFinalState;
use Magento\InventoryReservationCli\Model\GetOrdersWithNotCompensatedReservations;
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

        /** @var GetOrdersWithNotCompensatedReservations $getOrdersWithNotCompensatedReservations */
        $getOrdersWithNotCompensatedReservations = $objectManager->get(GetOrdersWithNotCompensatedReservations::class);

        /** @var GetOrdersInFinalState $getOrderInFinalState */
        $getOrderInFinalState = $objectManager->get(GetOrdersInFinalState::class);

        /** @var array $itemsNotCompensated */
        $itemsNotCompensated = $getOrdersWithNotCompensatedReservations->execute();

        /** @var OrderInterface[] $orders */
        $orders = $getOrderInFinalState->execute(array_keys($itemsNotCompensated));

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

        /** @var GetOrdersWithNotCompensatedReservations $getOrdersWithNotCompensatedReservations */
        $getOrdersWithNotCompensatedReservations = $objectManager->get(GetOrdersWithNotCompensatedReservations::class);

        /** @var GetOrdersInFinalState $getOrderInFinalState */
        $getOrderInFinalState = $objectManager->get(GetOrdersInFinalState::class);

        /** @var array $itemsNotCompensated */
        $itemsNotCompensated = $getOrdersWithNotCompensatedReservations->execute();

        /** @var OrderInterface[] $orders */
        $orders = $getOrderInFinalState->execute(array_keys($itemsNotCompensated));

        self::assertCount(1, $orders);
    }
}

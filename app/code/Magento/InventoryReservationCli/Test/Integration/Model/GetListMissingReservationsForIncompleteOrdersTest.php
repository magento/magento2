<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Test\Integration\Model;

use Magento\InventoryReservationCli\Model\GetOrdersInNotFinalState;
use Magento\InventoryReservationCli\Model\GetOrdersWithMissingInitialReservations;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class GetListMissingReservationsForIncompleteOrdersTest extends TestCase
{
    /**
     * @var GetOrdersWithMissingInitialReservations
     */
    private $getOrdersWithMissingInitialReservations;

    /**
     * @var GetOrdersInNotFinalState
     */
    private $getOrdersInNotFinalState;

    /**
     * Initialize test dependencies
     */
    protected function setUp()
    {
        $this->getOrdersWithMissingInitialReservations
            = Bootstrap::getObjectManager()->get(GetOrdersWithMissingInitialReservations::class);
        $this->getOrdersInNotFinalState
            = Bootstrap::getObjectManager()->get(GetOrdersInNotFinalState::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservationCli/Test/Integration/_fixtures/create_incomplete_order_with_reservation.php
     */
    public function testShouldNotFindAnyInconsistency(): void
    {
        $incompleteOrders = $this->getOrdersInNotFinalState->execute();
        $missingReservations = $this->getOrdersWithMissingInitialReservations->execute($incompleteOrders);
        self::assertSame([], $missingReservations);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testShouldFindOneInconsistency(): void
    {
        $incompleteOrders = $this->getOrdersInNotFinalState->execute();
        $missingReservations = $this->getOrdersWithMissingInitialReservations->execute($incompleteOrders);
        self::assertCount(1, $missingReservations);
    }
}

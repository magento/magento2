<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model;

use Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency\AddCompletedOrdersToUnresolved;
use Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency\AddExistingReservations;
use Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency\AddExpectedReservations;
use Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency\Collector;
use Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency\CollectorFactory;
use Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency\RemoveReservationsWithoutRelevantOrder;
use Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency\RemoveResolvedReservations;

/**
 * Filter orders for missing initial reservation
 */
class GetSaleableQuantityInconsistencies
{
    /**
     * @var CollectorFactory
     */
    private $collectorFactory;

    /**
     * @var AddExpectedReservations
     */
    private $addExpectedReservations;

    /**
     * @var AddExistingReservations
     */
    private $addExistingReservations;

    /**
     * @var RemoveResolvedReservations
     */
    private $removeResolvedReservations;

    /**
     * @var AddCompletedOrdersToUnresolved
     */
    private $addCompletedOrdersToUnresolved;

    /**
     * @var RemoveReservationsWithoutRelevantOrder
     */
    private $removeReservationsWithoutRelevantOrder;

    /**
     * @param CollectorFactory $collectorFactory
     * @param AddExpectedReservations $addExpectedReservations
     * @param AddExistingReservations $addExistingReservations
     * @param RemoveResolvedReservations $removeResolvedReservations
     * @param AddCompletedOrdersToUnresolved $addCompletedOrdersToUnresolved
     * @param RemoveReservationsWithoutRelevantOrder $removeReservationsWithoutRelevantOrder
     */
    public function __construct(
        CollectorFactory $collectorFactory,
        AddExpectedReservations $addExpectedReservations,
        AddExistingReservations $addExistingReservations,
        RemoveResolvedReservations $removeResolvedReservations,
        AddCompletedOrdersToUnresolved $addCompletedOrdersToUnresolved,
        RemoveReservationsWithoutRelevantOrder $removeReservationsWithoutRelevantOrder
    ) {
        $this->collectorFactory = $collectorFactory;
        $this->addExpectedReservations = $addExpectedReservations;
        $this->addExistingReservations = $addExistingReservations;
        $this->removeResolvedReservations = $removeResolvedReservations;
        $this->addCompletedOrdersToUnresolved = $addCompletedOrdersToUnresolved;
        $this->removeReservationsWithoutRelevantOrder = $removeReservationsWithoutRelevantOrder;
    }

    /**
     * Filter orders for missing initial reservation
     * @return SaleableQuantityInconsistency[]
     */
    public function execute(): array
    {
        /** @var Collector $collector */
        $collector = $this->collectorFactory->create();
        $this->addExpectedReservations->execute($collector);
        $this->addExistingReservations->execute($collector);
        $this->removeResolvedReservations->execute($collector);
        $this->addCompletedOrdersToUnresolved->execute($collector);
        $this->removeReservationsWithoutRelevantOrder->execute($collector);
        return $collector->getInconsistencies();
    }
}

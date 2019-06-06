<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException;
use Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\AddCompletedOrdersToForUnresolvedReservations;
use Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\AddExistingReservations;
use Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\AddExpectedReservations;
use Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\Collector;
use Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\CollectorFactory;
use Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\FilterExistingOrders;
use Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\FilterManagedStockProducts;
use Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\FilterUnresolvedReservations;

/**
 * Filter orders for missing initial reservation
 */
class GetSalableQuantityInconsistencies
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
     * @var AddCompletedOrdersToForUnresolvedReservations
     */
    private $addCompletedOrdersToUnresolved;

    /**
     * @var FilterExistingOrders
     */
    private $filterExistingOrders;

    /**
     * @var FilterUnresolvedReservations
     */
    private $filterUnresolvedReservations;

    /**
     * @var FilterManagedStockProducts
     */
    private $filterManagedStockProducts;

    /**
     * @param CollectorFactory $collectorFactory
     * @param AddExpectedReservations $addExpectedReservations
     * @param AddExistingReservations $addExistingReservations
     * @param AddCompletedOrdersToForUnresolvedReservations $addCompletedOrdersToUnresolved
     * @param FilterExistingOrders $filterExistingOrders
     * @param FilterUnresolvedReservations $filterUnresolvedReservations
     * @param FilterManagedStockProducts $filterManagedStockProducts
     */
    public function __construct(
        CollectorFactory $collectorFactory,
        AddExpectedReservations $addExpectedReservations,
        AddExistingReservations $addExistingReservations,
        AddCompletedOrdersToForUnresolvedReservations $addCompletedOrdersToUnresolved,
        FilterExistingOrders $filterExistingOrders,
        FilterUnresolvedReservations $filterUnresolvedReservations,
        FilterManagedStockProducts $filterManagedStockProducts
    ) {
        $this->collectorFactory = $collectorFactory;
        $this->addExpectedReservations = $addExpectedReservations;
        $this->addExistingReservations = $addExistingReservations;
        $this->addCompletedOrdersToUnresolved = $addCompletedOrdersToUnresolved;
        $this->filterExistingOrders = $filterExistingOrders;
        $this->filterUnresolvedReservations = $filterUnresolvedReservations;
        $this->filterManagedStockProducts = $filterManagedStockProducts;
    }

    /**
     * Filter orders for missing initial reservation
     * @return SalableQuantityInconsistency[]
     * @throws ValidationException
     * @throws LocalizedException
     * @throws SkuIsNotAssignedToStockException
     */
    public function execute(): array
    {
        /** @var Collector $collector */
        $collector = $this->collectorFactory->create();
        $this->addExpectedReservations->execute($collector);
        $this->addExistingReservations->execute($collector);
        $this->addCompletedOrdersToUnresolved->execute($collector);

        $items = $collector->getItems();
        $items = $this->filterManagedStockProducts->execute($items);
        $items = $this->filterUnresolvedReservations->execute($items);
        $items = $this->filterExistingOrders->execute($items);

        return $items;
    }
}

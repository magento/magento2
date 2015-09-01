<?php

namespace Magento\CatalogInventory\Api;

use Magento\CatalogInventory\Api\Data\InventoryReservationInterface;

/**
 * Inventory reservation management interface
 *
 * Provides all the methods, that are required for changes in stock on the frontend
 *
 */
interface InventoryReservationManagementInterface
{
    /**
     * Creates a single reservation for a request/response pair
     *
     * Should be used only in case if developer would like to
     * reserve stock separately from checkout process
     *
     * @param InventoryRequestInterface $request
     * @param InventoryResponseInterface $response
     * @return InventoryReservationInterface
     */
    public function createReservation(InventoryRequestInterface $request, InventoryResponseInterface $response);

    /**
     * Creates reservations for each supplied response/request pair
     *
     * It can be used before placing an order in Magento,
     * in order to be able reserve a stock via simple database query that will not produce a deadlock
     *
     * @param InventoryRequestInterface[] $requests
     * @param InventoryResponseInterface[] $responses
     * @return InventoryReservationInterface[]
     */
    public function createReservations($requests, $responses);

    /**
     * Splits reservation instance into specified quantities
     *
     * Will be very handy in case of partial reservation cancels or returns
     *
     * Method should remove old inventory reservation and replace it with new ones.
     *
     * @param InventoryReservationInterface $reservation
     * @param float[] $quantitiesSplit
     * @return InventoryReservationInterface[]
     */
    public function splitReservation(InventoryReservationInterface $reservation, array $quantitiesSplit);

    /**
     * Confirms reservations
     *
     * Should be used after order is placed in Magento, within transaction
     *
     * In case if any of the returned reservations have error status,
     * than the whole transactions should rollback, in that case there is no need to cancel reservations manually,
     * as reservation instances are meant to be immutable
     *
     * @param InventoryReservationInterface[] $reservations
     * @return InventoryReservationInterface[]
     */
    public function confirmReservations($reservations);

    /**
     * Confirms reservations
     *
     * Should be used after order is paid in Magento. This status change finalizes
     * the stock reservation and makes it not possible to cancel.
     *
     * @param InventoryReservationInterface[] $reservations
     * @return InventoryReservationInterface[]
     */
    public function completeReservations($reservations);

    /**
     * Cancels confirmed reservations
     *
     * It can be used if payment has failed for an order
     * or some specific timeout has passed for it.
     *
     * @param InventoryReservationInterface[] $reservations
     * @return InventoryReservationInterface[]
     */
    public function cancelReservations($reservations);

    /**
     * Returns completed reservations
     *
     * Should be used to return quantity from completed reservation to stock.
     *
     * @param InventoryReservationInterface[] $reservations
     * @return InventoryReservationInterface[]
     */
    public function returnReservations($reservations);
}

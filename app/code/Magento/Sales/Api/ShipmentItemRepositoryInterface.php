<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Shipment item repository interface.
 *
 * A shipment is a delivery package that contains products. A shipment document accompanies the shipment. This
 * document lists the products and their quantities in the delivery package. A product is an item in a shipment.
 * @api
 */
interface ShipmentItemRepositoryInterface
{
    /**
     * Lists shipment items that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria The search criteria.
     * @return \Magento\Sales\Api\Data\ShipmentItemSearchResultInterface Shipment item search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);

    /**
     * Loads a specified shipment item.
     *
     * @param int $id
     * @return \Magento\Sales\Api\Data\ShipmentItemInterface
     */
    public function get($id);

    /**
     * Deletes a specified shipment item.
     *
     * @param \Magento\Sales\Api\Data\ShipmentItemInterface $entity The shipment item.
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\ShipmentItemInterface $entity);

    /**
     * Performs persist operations for a specified shipment item.
     *
     * @param \Magento\Sales\Api\Data\ShipmentItemInterface $entity The shipment item.
     * @return \Magento\Sales\Api\Data\ShipmentItemInterface Shipment interface.
     */
    public function save(\Magento\Sales\Api\Data\ShipmentItemInterface $entity);
}

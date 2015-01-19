<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Shipment repository interface.
 *
 * A shipment is a delivery package that contains products. A shipment document accompanies the shipment. This
 * document lists the products and their quantities in the delivery package.
 */
interface ShipmentRepositoryInterface
{
    /**
     * Lists shipments that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteria $criteria The search criteria.
     * @return \Magento\Sales\Api\Data\ShipmentSearchResultInterface Shipment search results interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $criteria);

    /**
     * Loads a specified shipment.
     *
     * @param int $id The shipment ID.
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     */
    public function get($id);

    /**
     * Deletes a specified shipment.
     *
     * @param \Magento\Sales\Api\Data\ShipmentInterface $entity The shipment.
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\ShipmentInterface $entity);

    /**
     * Performs persist operations for a specified shipment.
     *
     * @param \Magento\Sales\Api\Data\ShipmentInterface $entity The shipment.
     * @return \Magento\Sales\Api\Data\ShipmentInterface Shipment interface.
     */
    public function save(\Magento\Sales\Api\Data\ShipmentInterface $entity);
}

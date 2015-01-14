<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Shipment item repository interface.
 *
 * A shipment is a delivery package that contains products. A shipment document accompanies the shipment. This
 * document lists the products and their quantities in the delivery package. A product is an item in a shipment.
 */
interface ShipmentItemRepositoryInterface
{
    /**
     * Lists shipment items that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteria $criteria The search criteria.
     * @return \Magento\Sales\Api\Data\ShipmentItemSearchResultInterface Shipment item search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $criteria);

    /**
     * Loads a specified shipment item.
     *
     * @param int $id
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     */
    public function get($id);

    /**
     * Deletes a specified shipment item.
     *
     * @param \Magento\Sales\Api\Data\ShipmentInterface $entity The shipment item.
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\ShipmentInterface $entity);

    /**
     * Performs persist operations for a specified shipment item.
     *
     * @param \Magento\Sales\Api\Data\ShipmentInterface $entity The shipment item.
     * @return \Magento\Sales\Api\Data\ShipmentInterface Shipment interface.
     */
    public function save(\Magento\Sales\Api\Data\ShipmentInterface $entity);
}

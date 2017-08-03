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
 * @since 2.0.0
 */
interface ShipmentItemRepositoryInterface
{
    /**
     * Lists shipment items that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @return \Magento\Sales\Api\Data\ShipmentItemSearchResultInterface Shipment item search result interface.
     * @since 2.0.0
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Loads a specified shipment item.
     *
     * @param int $id
     * @return \Magento\Sales\Api\Data\ShipmentItemInterface
     * @since 2.0.0
     */
    public function get($id);

    /**
     * Deletes a specified shipment item.
     *
     * @param \Magento\Sales\Api\Data\ShipmentItemInterface $entity The shipment item.
     * @return bool
     * @since 2.0.0
     */
    public function delete(\Magento\Sales\Api\Data\ShipmentItemInterface $entity);

    /**
     * Performs persist operations for a specified shipment item.
     *
     * @param \Magento\Sales\Api\Data\ShipmentItemInterface $entity The shipment item.
     * @return \Magento\Sales\Api\Data\ShipmentItemInterface Shipment interface.
     * @since 2.0.0
     */
    public function save(\Magento\Sales\Api\Data\ShipmentItemInterface $entity);
}

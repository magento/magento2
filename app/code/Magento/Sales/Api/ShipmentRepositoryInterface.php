<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Shipment repository interface.
 *
 * A shipment is a delivery package that contains products. A shipment document accompanies the shipment. This
 * document lists the products and their quantities in the delivery package.
 * @api
 */
interface ShipmentRepositoryInterface
{
    /**
     * Lists shipments that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included. See http://devdocs.magento.com/codelinks/attributes.html#ShipmentRepositoryInterface to
     * determine which call to use to get detailed information about all attributes for an object.
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria The search criteria.
     * @return \Magento\Sales\Api\Data\ShipmentSearchResultInterface Shipment search results interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);

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

    /**
     * Creates new shipment instance.
     *
     * @return \Magento\Sales\Api\Data\ShipmentInterface Shipment interface.
     */
    public function create();
}

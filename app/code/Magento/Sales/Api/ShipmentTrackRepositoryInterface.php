<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Api;

/**
 * Interface ShipmentTrackRepositoryInterface
 */
interface ShipmentTrackRepositoryInterface
{
    /**
     * @param \Magento\Framework\Api\SearchCriteria $criteria
     * @return \Magento\Sales\Api\Data\ShipmentTrackSearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $criteria);

    /**
     * Load entity
     *
     * @param int $id
     * @return \Magento\Sales\Api\Data\ShipmentTrackInterface
     */
    public function get($id);

    /**
     * Delete entity
     *
     * @param \Magento\Sales\Api\Data\ShipmentTrackInterface $entity
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\ShipmentTrackInterface $entity);

    /**
     * Perform persist operations for one entity
     *
     * @param \Magento\Sales\Api\Data\ShipmentTrackInterface $entity
     * @return \Magento\Sales\Api\Data\ShipmentTrackInterface
     */
    public function save(\Magento\Sales\Api\Data\ShipmentTrackInterface $entity);

    /**
     * Delete entity by Id
     * @param int $id
     * @return bool
     */
    public function deleteById($id);
}

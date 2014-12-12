<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api;

/**
 * Interface ShipmentCommentRepositoryInterface
 */
interface ShipmentCommentRepositoryInterface
{
    /**
     * @param \Magento\Framework\Api\SearchCriteria $criteria
     * @return \Magento\Sales\Api\Data\ShipmentCommentSearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $criteria);

    /**
     * Load entity
     *
     * @param int $id
     * @return \Magento\Sales\Api\Data\ShipmentCommentInterface
     */
    public function get($id);

    /**
     * Delete entity
     *
     * @param \Magento\Sales\Api\Data\ShipmentCommentInterface $entity
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\ShipmentCommentInterface $entity);

    /**
     * Perform persist operations for one entity
     *
     * @param \Magento\Sales\Api\Data\ShipmentCommentInterface $entity
     * @return \Magento\Sales\Api\Data\ShipmentCommentInterface
     */
    public function save(\Magento\Sales\Api\Data\ShipmentCommentInterface $entity);
}

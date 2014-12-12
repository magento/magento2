<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api;

/**
 * Interface RepositoryInterface
 */
interface CreditmemoRepositoryInterface
{
    /**
     * @param \Magento\Framework\Api\SearchCriteria $criteria
     * @return \Magento\Sales\Api\Data\CreditmemoSearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $criteria);

    /**
     * Load entity
     *
     * @param int $id
     * @return \Magento\Sales\Api\Data\CreditmemoInterface
     */
    public function get($id);

    /**
     * Delete entity
     *
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $entity
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\CreditmemoInterface $entity);

    /**
     * Perform persist operations for one entity
     *
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $entity
     * @return \Magento\Sales\Api\Data\CreditmemoInterface
     */
    public function save(\Magento\Sales\Api\Data\CreditmemoInterface $entity);
}

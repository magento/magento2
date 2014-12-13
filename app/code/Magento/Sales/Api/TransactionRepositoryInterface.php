<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api;

/**
 * Interface TransactionRepositoryInterface
 */
interface TransactionRepositoryInterface
{
    /**
     * @param \Magento\Framework\Api\SearchCriteria $criteria
     * @return \Magento\Sales\Api\Data\TransactionSearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $criteria);

    /**
     * Load entity
     *
     * @param int $id
     * @return \Magento\Sales\Api\Data\TransactionInterface
     */
    public function get($id);

    /**
     * Delete entity
     *
     * @param \Magento\Sales\Api\Data\TransactionInterface $entity
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\TransactionInterface $entity);

    /**
     * Perform persist operations for one entity
     *
     * @param \Magento\Sales\Api\Data\TransactionInterface $entity
     * @return \Magento\Sales\Api\Data\TransactionInterface
     */
    public function save(\Magento\Sales\Api\Data\TransactionInterface $entity);
}

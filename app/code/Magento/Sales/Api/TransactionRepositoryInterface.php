<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Transaction repository interface.
 *
 * A transaction is an interaction between a merchant and a customer such as a purchase, a credit, a refund, and so on.
 */
interface TransactionRepositoryInterface
{
    /**
     * Lists transactions that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteria $criteria The search criteria.
     * @return \Magento\Sales\Api\Data\TransactionSearchResultInterface Transaction search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $criteria);

    /**
     * Loads a specified transaction.
     *
     * @param int $id The transaction ID.
     * @return \Magento\Sales\Api\Data\TransactionInterface Transaction interface.
     */
    public function get($id);

    /**
     * Deletes a specified transaction.
     *
     * @param \Magento\Sales\Api\Data\TransactionInterface $entity The transaction.
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\TransactionInterface $entity);

    /**
     * Performs persist operations for a specified transaction.
     *
     * @param \Magento\Sales\Api\Data\TransactionInterface $entity The transaction.
     * @return \Magento\Sales\Api\Data\TransactionInterface Transaction interface.
     */
    public function save(\Magento\Sales\Api\Data\TransactionInterface $entity);
}

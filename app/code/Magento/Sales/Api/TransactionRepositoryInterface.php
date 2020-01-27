<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Transaction repository interface.
 *
 * A transaction is an interaction between a merchant and a customer such as a purchase, a credit, a refund, and so on.
 * @api
 * @since 100.0.2
 */
interface TransactionRepositoryInterface
{
    /**
     * Lists transactions that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included. See https://devdocs.magento.com/codelinks/attributes.html#TransactionRepositoryInterface to
     * determine which call to use to get detailed information about all attributes for an object.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @return \Magento\Sales\Api\Data\TransactionSearchResultInterface Transaction search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

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

    /**
     * Creates new Transaction instance.
     *
     * @return \Magento\Sales\Api\Data\TransactionInterface Transaction interface.
     */
    public function create();
}

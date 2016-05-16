<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Credit memo repository interface.
 *
 * After a customer places and pays for an order and an invoice has been issued, the merchant can create a credit memo
 * to refund all or part of the amount paid for any returned or undelivered items. The memo restores funds to the
 * customer account so that the customer can make future purchases.
 * @api
 */
interface CreditmemoRepositoryInterface
{
    /**
     * Lists credit memos that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included. See http://devdocs.magento.com/codelinks/attributes.html#CreditmemoRepositoryInterface to
     * determine which call to use to get detailed information about all attributes for an object.
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria The search criteria.
     * @return \Magento\Sales\Api\Data\CreditmemoSearchResultInterface Credit memo search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);

    /**
     * Loads a specified credit memo.
     *
     * @param int $id The credit memo ID.
     * @return \Magento\Sales\Api\Data\CreditmemoInterface Credit memo interface.
     */
    public function get($id);

    /**
     * Create credit memo instance
     *
     * @return \Magento\Sales\Api\Data\CreditmemoInterface Credit memo interface.
     */
    public function create();

    /**
     * Deletes a specified credit memo.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $entity The credit memo.
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\CreditmemoInterface $entity);

    /**
     * Performs persist operations for a specified credit memo.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $entity The credit memo.
     * @return \Magento\Sales\Api\Data\CreditmemoInterface Credit memo interface.
     */
    public function save(\Magento\Sales\Api\Data\CreditmemoInterface $entity);
}

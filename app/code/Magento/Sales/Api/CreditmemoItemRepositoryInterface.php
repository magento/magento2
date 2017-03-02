<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Credit memo item repository interface.
 *
 * After a customer places and pays for an order and an invoice has been issued, the merchant can create a credit memo
 * to refund all or part of the amount paid for any returned or undelivered items. The memo restores funds to the
 * customer account so that the customer can make future purchases. A credit memo item is an invoiced item for which
 * a merchant creates a credit memo.
 * @api
 */
interface CreditmemoItemRepositoryInterface
{
    /**
     * Loads a specified credit memo item.
     *
     * @param int $id The credit memo item ID.
     * @return \Magento\Sales\Api\Data\CreditmemoItemInterface Credit memo item interface.
     */
    public function get($id);

    /**
     * Lists credit memo items that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria The search criteria.
     * @return \Magento\Sales\Api\Data\CreditmemoItemSearchResultInterface Credit memo item search results interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);

    /**
     * Deletes a specified credit memo item.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoItemInterface $entity The credit memo item.
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\CreditmemoItemInterface $entity);

    /**
     * Performs persist operations for a specified credit memo item.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoItemInterface $entity The credit memo item.
     * @return \Magento\Sales\Api\Data\CreditmemoItemInterface Credit memo interface.
     */
    public function save(\Magento\Sales\Api\Data\CreditmemoItemInterface $entity);
}

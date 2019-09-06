<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Interface GetCustomerOrdersHistoryInterface
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
 * @api
 */
interface GetCustomerOrdersHistoryInterface
{
    /**
     * Find current customer orders by criteria
     *
     * @param int $customerId
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     */
    public function execute(
        int $customerId,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ) : \Magento\Sales\Api\Data\OrderSearchResultInterface;
}

<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Order payment repository interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
 * @api
 */
interface OrderPaymentRepositoryInterface
{
    /**
     * Lists order payments that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria The search criteria.
     * @return \Magento\Sales\Api\Data\OrderPaymentSearchResultInterface Order payment search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);

    /**
     * Loads a specified order payment.
     *
     * @param int $id The order payment ID.
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface Order payment interface.
     */
    public function get($id);

    /**
     * Deletes a specified order payment.
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $entity The order payment ID.
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\OrderPaymentInterface $entity);

    /**
     * Performs persist operations for a specified order payment.
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $entity The order payment ID.
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface Order payment interface.
     */
    public function save(\Magento\Sales\Api\Data\OrderPaymentInterface $entity);

    /**
     * Creates new Order Payment instance.
     *
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface Transaction interface.
     */
    public function create();
}

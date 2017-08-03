<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Order item repository interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
 * @api
 * @since 2.0.0
 */
interface OrderItemRepositoryInterface
{
    /**
     * Lists order items that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included. See http://devdocs.magento.com/codelinks/attributes.html#OrderItemRepositoryInterface to
     * determine which call to use to get detailed information about all attributes for an object.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @return \Magento\Sales\Api\Data\OrderItemSearchResultInterface Order item search result interface.
     * @since 2.0.0
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Loads a specified order item.
     *
     * @param int $id The order item ID.
     * @return \Magento\Sales\Api\Data\OrderItemInterface Order item interface.
     * @since 2.0.0
     */
    public function get($id);

    /**
     * Deletes a specified order item.
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $entity The order item.
     * @return bool
     * @since 2.0.0
     */
    public function delete(\Magento\Sales\Api\Data\OrderItemInterface $entity);

    /**
     * Performs persist operations for a specified order item.
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $entity The order item.
     * @return \Magento\Sales\Api\Data\OrderItemInterface Order item interface.
     * @since 2.0.0
     */
    public function save(\Magento\Sales\Api\Data\OrderItemInterface $entity);
}

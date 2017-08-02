<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Order address repository interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
 * @api
 * @since 2.0.0
 */
interface OrderAddressRepositoryInterface
{
    /**
     * Lists order addresses that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @return \Magento\Sales\Api\Data\OrderAddressSearchResultInterface Order address search result interface.
     * @since 2.0.0
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Loads a specified order address.
     *
     * @param int $id The order address ID.
     * @return \Magento\Sales\Api\Data\OrderAddressInterface Order address interface.
     * @since 2.0.0
     */
    public function get($id);

    /**
     * Deletes a specified order address.
     *
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $entity The order address.
     * @return bool
     * @since 2.0.0
     */
    public function delete(\Magento\Sales\Api\Data\OrderAddressInterface $entity);

    /**
     * Performs persist operations for a specified order address.
     *
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $entity The order address.
     * @return \Magento\Sales\Api\Data\OrderAddressInterface Order address interface.
     * @since 2.0.0
     */
    public function save(\Magento\Sales\Api\Data\OrderAddressInterface $entity);
}

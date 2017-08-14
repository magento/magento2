<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Invoice item repository interface.
 *
 * An invoice is a record of the receipt of payment for an order. An invoice item is a purchased item in an invoice.
 * @api
 * @since 100.0.2
 */
interface InvoiceItemRepositoryInterface
{
    /**
     * Lists the invoice items that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Sales\Api\Data\InvoiceItemSearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Loads a specified invoice item.
     *
     * @param int $id The invoice item ID.
     * @return \Magento\Sales\Api\Data\InvoiceItemInterface Invoice item interface.
     */
    public function get($id);

    /**
     * Deletes a specified invoice item.
     *
     * @param \Magento\Sales\Api\Data\InvoiceItemInterface $entity The invoice item.
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\InvoiceItemInterface $entity);

    /**
     * Performs persist operations for a specified invoice item.
     *
     * @param \Magento\Sales\Api\Data\InvoiceItemInterface $entity The invoice item.
     * @return \Magento\Sales\Api\Data\InvoiceItemInterface Invoice item interface.
     */
    public function save(\Magento\Sales\Api\Data\InvoiceItemInterface $entity);
}

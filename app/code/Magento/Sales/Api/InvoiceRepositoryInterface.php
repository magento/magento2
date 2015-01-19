<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Invoice repository interface.
 *
 * An invoice is a record of the receipt of payment for an order.
 */
interface InvoiceRepositoryInterface
{
    /**
     * Lists invoices that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteria $criteria The search criteria.
     * @return \Magento\Sales\Api\Data\InvoiceSearchResultInterface Invoice search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $criteria);

    /**
     * Loads a specified invoice.
     *
     * @param int $id The invoice ID.
     * @return \Magento\Sales\Api\Data\InvoiceInterface Invoice interface.
     */
    public function get($id);

    /**
     * Deletes a specified invoice.
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface $entity The invoice.
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\InvoiceInterface $entity);

    /**
     * Performs persist operations for a specified invoice.
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface $entity The invoice.
     * @return \Magento\Sales\Api\Data\InvoiceInterface Invoice interface.
     */
    public function save(\Magento\Sales\Api\Data\InvoiceInterface $entity);
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Invoice repository interface.
 *
 * An invoice is a record of the receipt of payment for an order.
 * @api
 * @since 2.0.0
 */
interface InvoiceRepositoryInterface
{
    /**
     * Lists invoices that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included. See http://devdocs.magento.com/codelinks/attributes.html#InvoiceRepositoryInterface to
     * determine which call to use to get detailed information about all attributes for an object.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @return \Magento\Sales\Api\Data\InvoiceSearchResultInterface Invoice search result interface.
     * @since 2.0.0
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Return Invoice object
     *
     * @return \Magento\Sales\Api\Data\InvoiceInterface
     * @since 2.0.0
     */
    public function create();

    /**
     * Loads a specified invoice.
     *
     * @param int $id The invoice ID.
     * @return \Magento\Sales\Api\Data\InvoiceInterface Invoice interface.
     * @since 2.0.0
     */
    public function get($id);

    /**
     * Deletes a specified invoice.
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface $entity The invoice.
     * @return bool
     * @since 2.0.0
     */
    public function delete(\Magento\Sales\Api\Data\InvoiceInterface $entity);

    /**
     * Performs persist operations for a specified invoice.
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface $entity The invoice.
     * @return \Magento\Sales\Api\Data\InvoiceInterface Invoice interface.
     * @since 2.0.0
     */
    public function save(\Magento\Sales\Api\Data\InvoiceInterface $entity);
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Invoice comment repository interface.
 *
 * An invoice is a record of the receipt of payment for an order. An invoice can include comments that detail the
 * invoice history.
 * @api
 */
interface InvoiceCommentRepositoryInterface
{
    /**
     * Lists invoice comments that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @return \Magento\Sales\Api\Data\InvoiceCommentSearchResultInterface Invoice search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Loads a specified invoice comment.
     *
     * @param int $id The invoice comment ID.
     * @return \Magento\Sales\Api\Data\InvoiceCommentInterface Invoice comment interface.
     */
    public function get($id);

    /**
     * Deletes a specified invoice comment.
     *
     * @param \Magento\Sales\Api\Data\InvoiceCommentInterface $entity The invoice comment.
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\InvoiceCommentInterface $entity);

    /**
     * Performs persist operations for a specified invoice comment.
     *
     * @param \Magento\Sales\Api\Data\InvoiceCommentInterface $entity The invoice comment.
     * @return \Magento\Sales\Api\Data\InvoiceCommentInterface Invoice comment interface.
     */
    public function save(\Magento\Sales\Api\Data\InvoiceCommentInterface $entity);
}

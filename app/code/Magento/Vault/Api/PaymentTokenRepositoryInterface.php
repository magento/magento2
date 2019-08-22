<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Api;

/**
 * Gateway vault payment token repository interface.
 *
 * @api
 * @since 100.1.0
 */
interface PaymentTokenRepositoryInterface
{
    /**
     * Lists payment tokens that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @return \Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface Payment token search result interface.
     * @since 100.1.0
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Loads a specified payment token.
     *
     * @param int $entityId The payment token entity ID.
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface Payment token interface.
     * @since 100.1.0
     */
    public function getById($entityId);

    /**
     * Deletes a specified payment token.
     *
     * @param \Magento\Vault\Api\Data\PaymentTokenInterface $paymentToken The invoice.
     * @return bool
     * @since 100.1.0
     */
    public function delete(Data\PaymentTokenInterface $paymentToken);

    /**
     * Performs persist operations for a specified payment token.
     *
     * @param \Magento\Vault\Api\Data\PaymentTokenInterface $paymentToken The payment token.
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface Payment token interface.
     * @since 100.1.0
     */
    public function save(Data\PaymentTokenInterface $paymentToken);
}

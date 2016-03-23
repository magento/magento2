<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Api;

/**
 * Gateway vault payment token repository interface.
 *
 * @api
 */
interface PaymentTokenRepositoryInterface
{
    /**
     * Lists payment tokens that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria The search criteria.
     * @return \Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface Payment token search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);

    /**
     * Loads a specified payment token.
     *
     * @param int $entityId The payment token entity ID.
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface Payment token interface.
     */
    public function getById($entityId);

    /**
     * Deletes a specified payment token.
     *
     * @param \Magento\Vault\Api\Data\PaymentTokenInterface $paymentToken The invoice.
     * @return bool
     */
    public function delete(Data\PaymentTokenInterface $paymentToken);

    /**
     * Performs persist operations for a specified payment token.
     *
     * @param \Magento\Vault\Api\Data\PaymentTokenInterface $paymentToken The payment token.
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface Payment token interface.
     */
    public function save(Data\PaymentTokenInterface $paymentToken);
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Api;

/**
 * Interface CheckoutAgreementsListingInterface
 *
 * @api
 */
interface CheckoutAgreementsListingInterface
{
    /**
     * Listing of checkout agreements.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\CheckoutAgreements\Api\Data\AgreementInterface[]
     */
    public function getListing(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria) : array;
}

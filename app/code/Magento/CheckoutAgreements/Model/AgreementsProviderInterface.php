<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Model;

/**
 * Interface AgreementsProviderInterface
 */
interface AgreementsProviderInterface
{
    /**
     * Get list of Required Agreement Ids
     *
     * @return int[]
     */
    public function getRequiredAgreementIds();
}

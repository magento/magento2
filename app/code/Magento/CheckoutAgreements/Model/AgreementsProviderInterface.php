<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Model;

/**
 * Interface AgreementsProviderInterface
 * @since 2.0.0
 */
interface AgreementsProviderInterface
{
    /**
     * Get list of Required Agreement Ids
     *
     * @return int[]
     * @since 2.0.0
     */
    public function getRequiredAgreementIds();
}

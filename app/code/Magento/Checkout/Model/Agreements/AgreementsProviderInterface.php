<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Agreements;

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

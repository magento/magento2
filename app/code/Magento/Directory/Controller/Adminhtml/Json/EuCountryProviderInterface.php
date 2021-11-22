<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Controller\Adminhtml\Json;

interface EuCountryProviderInterface
{
    /**
     * Check if the Country is in EU Country list
     *
     * @param $countryCode
     * @return string
     */
    public function isEuCountry($countryCode): string;
}

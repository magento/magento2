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
     * @param $countryCode
     * @return string
     */
    public function isEuCountry($countryCode): string;
}

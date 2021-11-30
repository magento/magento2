<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Country extends AbstractHelper
{
    /**
     * Config path to UE country list
     */
    const XML_PATH_EU_COUNTRIES_LIST = 'general/country/eu_countries';

    /**
     * Fetch EU countries list
     *
     * @param  null $storeId
     * @return false|string[]
     */
    public function getEuCountryList($storeId=null)
    {
        $euCountries = explode(
            ',',
            $this->scopeConfig->getValue(
                self::XML_PATH_EU_COUNTRIES_LIST,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );

        return $euCountries;
    }
}

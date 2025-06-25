<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class CountryFilter implements ArgumentInterface
{
    /**
     * Config path to UE country list
     */
    private const XML_PATH_EU_COUNTRIES_LIST = 'general/country/eu_countries';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Fetch EU countries list
     *
     * @param  Int|null $storeId
     * @return array
     */
    public function getEuCountryList(?int $storeId = null): array
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

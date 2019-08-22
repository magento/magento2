<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class TopDestinationCountries
 */
class TopDestinationCountries
{
    const CONFIG_DESTINATIONS_PATH = 'general/country/destinations';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve list of top destinations countries
     *
     * @return array
     */
    public function getTopDestinations()
    {
        $destinations = (string)$this->scopeConfig->getValue(
            self::CONFIG_DESTINATIONS_PATH,
            ScopeInterface::SCOPE_STORE
        );
        return !empty($destinations) ? explode(',', $destinations) : [];
    }
}

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
 * @since 2.2.0
 */
class TopDestinationCountries
{
    const CONFIG_DESTINATIONS_PATH = 'general/country/destinations';

    /**
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @since 2.2.0
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve list of top destinations countries
     *
     * @return array
     * @since 2.2.0
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

<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Store\Model\ScopeInterface;

/**
 * Interface CountryHandlerInterface.
 * @package Magento\Directory\Model
 */
interface CountryHandlerInterface
{
    const ALLOWED_COUNTRIES_PATH = 'general/country/allow';

    /**
     * Retrieve all allowed in current scope countries.
     * @param null $filter
     * @param string $scope
     * @param bool $ignoreGlobalScope
     * @return array
     */
    public function getAllowedCountries(
        $filter = null,
        $scope = ScopeInterface::SCOPE_WEBSITE,
        $ignoreGlobalScope = false
    );

    /**
     * Filter directory collection by allowed in website countries.
     * @param $filter
     * @param string $scope
     * @param \Magento\Framework\Data\Collection\AbstractDb $collection
     * @return AbstractDb
     */
    public function loadByScope($filter, $scope = ScopeInterface::SCOPE_STORE, AbstractDb $collection);
}

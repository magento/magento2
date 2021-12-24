<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\ViewModel\Customer\Address;

use Magento\Customer\Model\ResourceModel\Address\Collection;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Customer's addresses filter as per allowed country filter for corresponding store
 */
class AddressAttributeFilter implements ArgumentInterface
{
    /**
     * @var AllowedCountries
     */
    private $allowedCountryReader;

    /**
     * @param AllowedCountries $allowedCountryReader
     */
    public function __construct(
        AllowedCountries $allowedCountryReader
    ) {
        $this->allowedCountryReader = $allowedCountryReader;
    }

    /**
     * Set allowed country filter for customer's addresses
     *
     * @param Collection $collection
     * @param string|integer $storeId
     * @return Collection
     * @throws LocalizedException
     */
    public function setScopeFilter(Collection $collection, $storeId) : Collection
    {
        if ($storeId) {
            $allowedCountries = $this->allowedCountryReader->getAllowedCountries(
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $collection->addAttributeToFilter('country_id', ['in' => $allowedCountries]);
        }

        return $collection;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Plugin;

use Magento\Customer\Model\ResourceModel\Address\Collection;
use Magento\Sales\Block\Adminhtml\Order\Create\Form\Address as AddressBlock;
use Magento\Store\Model\ScopeInterface;
use Magento\Directory\Model\AllowedCountries;

/**
 * Class AllowedCountriesAddressFilter
 */
class AllowedCountriesAddressFilter
{
    /**
     * @var AllowedCountries
     */
    private $allowedCountryReader;

    /**
     * @var AddressBlock
     */
    private $addressBlock;

    /**
     * @param AllowedCountries|null $allowedCountryReader
     * @param AddressBlock $addressBlock
     */
    public function __construct(
        AllowedCountries $allowedCountryReader,
        AddressBlock $addressBlock
    ) {
        $this->allowedCountryReader = $allowedCountryReader;
        $this->addressBlock = $addressBlock;
    }

    /**
     * Filter customer saved addresses by allowed countries of their store.
     *
     * @param \Magento\Customer\Model\ResourceModel\Address\Collection $subject
     * @param \Magento\Customer\Model\Customer|array $customer
     * @return $this
     */
    public function beforeSetCustomerFilter(Collection $subject, $customer)
    {
        $storeId = $this->addressBlock->getStoreId() ?? null;
        if ($storeId) {
            $allowedCountries = $this->allowedCountryReader->getAllowedCountries(
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $subject->addAttributeToFilter('country_id', ['in' => $allowedCountries]);
        }
    }
}

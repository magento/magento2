<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\System\Config\Source;

class Country implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Countries
     *
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $countryCollection;

    /**
     * Countries not supported by Braintree
     * List can be found in Braintree website https://support.braintreepayments.com/customer/portal/articles/1142481
     */
    protected $excludedCountries = [
        'MM', 'IR', 'SD', 'BY', 'CI', 'CD', 'CG', 'IQ', 'LR', 'LB', 'KP', 'SL', 'SY', 'ZW', 'AL', 'BA', 'MK', 'ME', 'RS'
    ];

    /**
     * @param \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection
     */
    public function __construct(\Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection)
    {
        $this->countryCollection = $countryCollection;
    }

    /**
     * @param bool $isMultiselect
     * @return array
     */
    public function toOptionArray($isMultiselect = false)
    {
        if (!$this->options) {
            $this->options = $this->countryCollection
                ->addFieldToFilter('country_id', ['nin' => $this->excludedCountries])
                ->loadData()
                ->toOptionArray(false);
        }

        $options = $this->options;
        if (!$isMultiselect) {
            array_unshift($options, ['value'=>'', 'label'=> __('--Please Select--')]);
        }

        return $options;
    }

    /**
     * If country is in list of restricted (not supported by Braintree)
     *
     * @param string $countryId
     * @return boolean
     */
    public function isCountryRestricted($countryId)
    {
        if (in_array($countryId, $this->excludedCountries)) {
            return true;
        }
        return false;
    }

    /**
     * Returns list of restricted countries
     *
     * @return array
     */
    public function getRestrictedCountries()
    {
        return $this->excludedCountries;
    }
}

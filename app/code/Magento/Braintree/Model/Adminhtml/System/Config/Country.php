<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Adminhtml\System\Config;

use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Country
 * @since 2.1.0
 */
class Country implements ArrayInterface
{
    /**
     * @var array
     * @since 2.1.0
     */
    protected $options;

    /**
     * Countries
     *
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     * @since 2.1.0
     */
    protected $countryCollection;

    /**
     * Countries not supported by Braintree
     * @since 2.1.0
     */
    protected $excludedCountries = [
        'MM',
        'IR',
        'SD',
        'BY',
        'CI',
        'CD',
        'CG',
        'IQ',
        'LR',
        'LB',
        'KP',
        'SL',
        'SY',
        'ZW',
        'AL',
        'BA',
        'MK',
        'ME',
        'RS'
    ];

    /**
     * @param \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection
     * @since 2.1.0
     */
    public function __construct(Collection $countryCollection)
    {
        $this->countryCollection = $countryCollection;
    }

    /**
     * @param bool $isMultiselect
     * @return array
     * @since 2.1.0
     */
    public function toOptionArray($isMultiselect = false)
    {
        if (!$this->options) {
            $this->options = $this->countryCollection
                ->addFieldToFilter('country_id', ['nin' => $this->getExcludedCountries()])
                ->loadData()
                ->toOptionArray(false);
        }

        $options = $this->options;
        if (!$isMultiselect) {
            array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);
        }

        return $options;
    }

    /**
     * If country is in list of restricted (not supported by Braintree)
     *
     * @param string $countryId
     * @return boolean
     * @since 2.1.0
     */
    public function isCountryRestricted($countryId)
    {
        return in_array($countryId, $this->getExcludedCountries());
    }

    /**
     * Return list of excluded countries
     * @return array
     * @since 2.1.0
     */
    public function getExcludedCountries()
    {
        return $this->excludedCountries;
    }
}

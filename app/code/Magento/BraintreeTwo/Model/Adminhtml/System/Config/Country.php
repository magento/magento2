<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Model\Adminhtml\System\Config;

use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Country
 * @package Magento\BraintreeTwo\Model\Adminhtml\System\Config
 */
class Country implements ArrayInterface
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
     */
    public static $excludedCountries = [
        'MM', 'IR', 'SD', 'BY', 'CI', 'CD', 'CG', 'IQ', 'LR', 'LB', 'KP', 'SL', 'SY', 'ZW', 'AL', 'BA', 'MK', 'ME', 'RS'
    ];

    /**
     * @param \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection
     */
    public function __construct(Collection $countryCollection)
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
                ->addFieldToFilter('country_id', ['nin' => self::$excludedCountries])
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
     */
    public function isCountryRestricted($countryId)
    {
        $keys = array_flip(self::$excludedCountries);
        return isset($keys[$countryId]);
    }
}

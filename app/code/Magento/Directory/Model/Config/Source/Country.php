<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Config\Source;

/**
 * Options provider for countries list
 *
 * @api
 * @since 2.0.0
 */
class Country implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Countries
     *
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     * @since 2.0.0
     */
    protected $_countryCollection;

    /**
     * @param \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection
     * @since 2.0.0
     */
    public function __construct(\Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection)
    {
        $this->_countryCollection = $countryCollection;
    }

    /**
     * Options array
     *
     * @var array
     * @since 2.0.0
     */
    protected $_options;

    /**
     * Return options array
     *
     * @param boolean $isMultiselect
     * @param string|array $foregroundCountries
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray($isMultiselect = false, $foregroundCountries = '')
    {
        if (!$this->_options) {
            $this->_options = $this->_countryCollection->loadData()->setForegroundCountries(
                $foregroundCountries
            )->toOptionArray(
                false
            );
        }

        $options = $this->_options;
        if (!$isMultiselect) {
            array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);
        }

        return $options;
    }
}

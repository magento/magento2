<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\ResourceModel\Country\Format;

/**
 * Country formats collection
 *
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define main table
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Directory\Model\Country\Format::class,
            \Magento\Directory\Model\ResourceModel\Country\Format::class
        );
    }

    /**
     * Set country filter
     *
     * @param string|\Magento\Directory\Model\Country $country
     * @return \Magento\Directory\Model\ResourceModel\Country\Format\Collection
     * @since 2.0.0
     */
    public function setCountryFilter($country)
    {
        if ($country instanceof \Magento\Directory\Model\Country) {
            $countryId = $country->getId();
        } else {
            $countryId = $country;
        }

        return $this->addFieldToFilter('country_id', $countryId);
    }
}

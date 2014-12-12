<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Directory\Model\Resource\Country\Format;

/**
 * \Directory country format resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Directory\Model\Country\Format', 'Magento\Directory\Model\Resource\Country\Format');
    }

    /**
     * Set country filter
     *
     * @param string|\Magento\Directory\Model\Country $country
     * @return \Magento\Directory\Model\Resource\Country\Format\Collection
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

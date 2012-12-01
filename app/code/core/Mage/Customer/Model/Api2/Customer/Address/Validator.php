<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Customer
 * @copyright  Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * API2 class for customer address rest
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Customer_Model_Api2_Customer_Address_Validator extends Mage_Api2_Model_Resource_Validator_Eav
{
    /**
     * Separator for multistreet
     */
    const STREET_SEPARATOR = '; ';

    /**
     * Filter request data.
     *
     * @param  array $data
     * @return array Filtered data
     */
    public function filter(array $data)
    {
        $filteredData = parent::filter($data);

        // If the array contains more than two elements, then combine the extra elements in a string
        if (isset($filteredData['street']) && is_array($filteredData['street']) && count($filteredData['street']) > 2) {
            $filteredData['street'][1] .= self::STREET_SEPARATOR
                . implode(self::STREET_SEPARATOR, array_slice($filteredData['street'], 2));
            $filteredData['street'] = array_slice($filteredData['street'], 0, 2);
        }
        // pass default addresses info
        if (isset($data['is_default_billing'])) {
            $filteredData['is_default_billing'] = $data['is_default_billing'];
        }
        if (isset($data['is_default_shipping'])) {
            $filteredData['is_default_shipping'] = $data['is_default_shipping'];
        }
        return $filteredData;
    }

    /**
     * Validate data for create association with the country
     *
     * @param array $data
     * @return bool
     */
    public function isValidDataForCreateAssociationWithCountry(array $data)
    {
        return $this->_checkRegion($data, Mage::getModel('Mage_Directory_Model_Country')->loadByCode($data['country_id']));
    }

    /**
     * Validate data for change association with the country
     *
     * @param Mage_Customer_Model_Address $address
     * @param array $data
     * @return bool
     */
    public function isValidDataForChangeAssociationWithCountry(Mage_Customer_Model_Address $address, array $data)
    {
        if (!isset($data['country_id']) && !isset($data['region'])) {
            return true;
        }
        // If country is in data - it has been already validated. If no - load current country.
        if (isset($data['country_id'])) {
            $country = Mage::getModel('Mage_Directory_Model_Country')->loadByCode($data['country_id']);
        } else {
            $country = $address->getCountryModel();
        }
        return $this->_checkRegion($data, $country);
    }

    /**
     * Check region
     *
     * @param array $data
     * @param Mage_Directory_Model_Country $country
     * @return bool
     */
    protected function _checkRegion($data, Mage_Directory_Model_Country $country)
    {
        /* @var $regions Mage_Directory_Model_Resource_Region_Collection */
        $regions = $country->getRegions();
        // Is it the country with predifined regions?
        if ($regions->count()) {
            if (!array_key_exists('region', $data) || empty($data['region'])) {
                $this->_addError('"State/Province" is required.');
                return false;
            }

            if (!is_string($data['region'])) {
                $this->_addError('Invalid "State/Province" type.');
                return false;
            }

            $count = $regions->addFieldToFilter(array('default_name', 'code'), array($data['region'], $data['region']))
                ->clear()
                ->count();
            if (!$count) {
                $this->_addError('State/Province does not exist.');
                return false;
            }
        } else {
            if (array_key_exists('region', $data) && !is_string($data['region'])) {
                $this->_addError('Invalid "State/Province" type.');
                return false;
            }
        }

        return true;
    }
}

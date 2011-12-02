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
 * @package     Mage_Directory
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Directory data helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Directory_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Config value that lists ISO2 country codes which have optional Zip/Postal pre-configured
     */
    const OPTIONAL_ZIP_COUNTRIES_CONFIG_PATH = 'general/country/optional_zip_countries';

    /**
     * Country collection
     *
     * @var Mage_Directory_Model_Resource_Country_Collection
     */
    protected $_countryCollection;

    /**
     * Region collection
     *
     * @var Mage_Directory_Model_Resource_Region_Collection
     */
    protected $_regionCollection;

    /**
     * Json representation of regions data
     *
     * @var string
     */
    protected $_regionJson;

    /**
     * Currency cache
     *
     * @var array
     */
    protected $_currencyCache = array();

    /**
     * ISO2 country codes which have optional Zip/Postal pre-configured
     *
     * @var array
     */
    protected $_optionalZipCountries = null;

    /**
     * Retrieve region collection
     *
     * @return Mage_Directory_Model_Resource_Region_Collection
     */
    public function getRegionCollection()
    {
        if (!$this->_regionCollection) {
            $this->_regionCollection = Mage::getModel('Mage_Directory_Model_Region')->getResourceCollection()
                ->addCountryFilter($this->getAddress()->getCountryId())
                ->load();
        }
        return $this->_regionCollection;
    }

    /**
     * Retrieve country collection
     *
     * @return Mage_Directory_Model_Resource_Country_Collection
     */
    public function getCountryCollection()
    {
        if (!$this->_countryCollection) {
            $this->_countryCollection = Mage::getModel('Mage_Directory_Model_Country')->getResourceCollection()
                ->loadByStore();
        }
        return $this->_countryCollection;
    }

    /**
     * Retrieve regions data json
     *
     * @return string
     */
    public function getRegionJson()
    {

        Magento_Profiler::start('TEST: '.__METHOD__);
        if (!$this->_regionJson) {
            $cacheKey = 'DIRECTORY_REGIONS_JSON_STORE'.Mage::app()->getStore()->getId();
            if (Mage::app()->useCache('config')) {
                $json = Mage::app()->loadCache($cacheKey);
            }
            if (empty($json)) {
                $countryIds = array();
                foreach ($this->getCountryCollection() as $country) {
                    $countryIds[] = $country->getCountryId();
                }
                $collection = Mage::getModel('Mage_Directory_Model_Region')->getResourceCollection()
                    ->addCountryFilter($countryIds)
                    ->load();
                $regions = array();
                foreach ($collection as $region) {
                    if (!$region->getRegionId()) {
                        continue;
                    }
                    $regions[$region->getCountryId()][$region->getRegionId()] = array(
                        'code' => $region->getCode(),
                        'name' => $this->__($region->getName())
                    );
                }
                $json = Mage::helper('Mage_Core_Helper_Data')->jsonEncode($regions);

                if (Mage::app()->useCache('config')) {
                    Mage::app()->saveCache($json, $cacheKey, array('config'));
                }
            }
            $this->_regionJson = $json;
        }

        Magento_Profiler::stop('TEST: '.__METHOD__);
        return $this->_regionJson;
    }

    /**
     * Convert currency
     *
     * @param float $amount
     * @param string $from
     * @param string $to
     * @return float
     */
    public function currencyConvert($amount, $from, $to = null)
    {
        if (empty($this->_currencyCache[$from])) {
            $this->_currencyCache[$from] = Mage::getModel('Mage_Directory_Model_Currency')->load($from);
        }
        if (is_null($to)) {
            $to = Mage::app()->getStore()->getCurrentCurrencyCode();
        }
        $converted = $this->_currencyCache[$from]->convert($amount, $to);
        return $converted;
    }

    /**
     * Return ISO2 country codes, which have optional Zip/Postal pre-configured
     *
     * @param bool $asJson
     * @return array|string
     */
    public function getCountriesWithOptionalZip($asJson = false)
    {
        if (null === $this->_optionalZipCountries) {
            $this->_optionalZipCountries = preg_split('/\,/',
                Mage::getStoreConfig(self::OPTIONAL_ZIP_COUNTRIES_CONFIG_PATH), 0, PREG_SPLIT_NO_EMPTY);
        }
        if ($asJson) {
            return Mage::helper('Mage_Core_Helper_Data')->jsonEncode($this->_optionalZipCountries);
        }
        return $this->_optionalZipCountries;
    }

    /**
     * Check whether zip code is optional for specified country code
     *
     * @param string $countryCode
     * @return boolean
     */
    public function isZipCodeOptional($countryCode)
    {
        $this->getCountriesWithOptionalZip();
        return in_array($countryCode, $this->_optionalZipCountries);
    }
}

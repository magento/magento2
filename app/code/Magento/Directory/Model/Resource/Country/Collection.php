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
 * @category    Magento
 * @package     Magento_Directory
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Directory Country Resource Collection
 */
namespace Magento\Directory\Model\Resource\Country;

class Collection extends \Magento\Core\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Locale model
     *
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Directory\Model\Resource\CountryFactory
     */
    protected $_countryFactory;

    /**
     * Array utils object
     *
     * @var \Magento\Stdlib\ArrayUtils
     */
    protected $_arrayUtils;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Logger $logger
     * @param \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Directory\Model\Resource\CountryFactory $countryFactory
     * @param \Magento\Stdlib\ArrayUtils $arrayUtils
     * @param mixed $connection
     * @param \Magento\Core\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Logger $logger,
        \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Directory\Model\Resource\CountryFactory $countryFactory,
        \Magento\Stdlib\ArrayUtils $arrayUtils,
        $connection = null,
        \Magento\Core\Model\Resource\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_locale = $locale;
        $this->_countryFactory = $countryFactory;
        $this->_arrayUtils = $arrayUtils;
    }
    /**
     * Foreground countries
     *
     * @var array
     */
    protected $_foregroundCountries = array();

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Directory\Model\Country', 'Magento\Directory\Model\Resource\Country');
    }

    /**
     * Load allowed countries for current store
     *
     * @param mixed $store
     * @return \Magento\Directory\Model\Resource\Country\Collection
     */
    public function loadByStore($store = null)
    {
        $allowCountries = explode(',', (string)$this->_coreStoreConfig->getConfig('general/country/allow', $store));
        if (!empty($allowCountries)) {
            $this->addFieldToFilter("country_id", array('in' => $allowCountries));
        }
        return $this;
    }

    /**
     * Loads Item By Id
     *
     * @param string $countryId
     * @return \Magento\Directory\Model\Resource\Country
     */
    public function getItemById($countryId)
    {
        foreach ($this->_items as $country) {
            if ($country->getCountryId() == $countryId) {
                return $country;
            }
        }
        return $this->_countryFactory->create();
    }

    /**
     * Add filter by country code to collection.
     * $countryCode can be either array of country codes or string representing one country code.
     * $iso can be either array containing 'iso2', 'iso3' values or string with containing one of that values directly.
     * The collection will contain countries where at least one of contry $iso fields matches $countryCode.
     *
     * @param string|string[] $countryCode
     * @param string|string[] $iso
     * @return $this
     */
    public function addCountryCodeFilter($countryCode, $iso = array('iso3', 'iso2'))
    {
        if (!empty($countryCode)) {
            if (is_array($countryCode)) {
                if (is_array($iso)) {
                    $whereOr = array();
                    foreach ($iso as $iso_curr) {
                        $whereOr[] .= $this->_getConditionSql("{$iso_curr}_code", array('in' => $countryCode));
                    }
                    $this->_select->where('(' . implode(') OR (', $whereOr) . ')');
                } else {
                    $this->addFieldToFilter("{$iso}_code", array('in' => $countryCode));
                }
            } else {
                if (is_array($iso)) {
                    $whereOr = array();
                    foreach ($iso as $iso_curr) {
                        $whereOr[] .= $this->_getConditionSql("{$iso_curr}_code", $countryCode);
                    }
                    $this->_select->where('(' . implode(') OR (', $whereOr) . ')');
                } else {
                    $this->addFieldToFilter("{$iso}_code", $countryCode);
                }
            }
        }
        return $this;
    }

    /**
     * Add filter by country code(s) to collection
     *
     * @param string|string[] $countryId
     * @return $this
     */
    public function addCountryIdFilter($countryId)
    {
        if (!empty($countryId)) {
            if (is_array($countryId)) {
                $this->addFieldToFilter("country_id", array('in' => $countryId));
            } else {
                $this->addFieldToFilter("country_id", $countryId);
            }
        }
        return $this;
    }

    /**
     * Convert collection items to select options array
     *
     * @param string|boolean $emptyLabel
     * @return array
     */
    public function toOptionArray($emptyLabel = ' ')
    {
        $options = $this->_toOptionArray('country_id', 'name', array('title' => 'iso2_code'));

        $sort = array();
        foreach ($options as $data) {
            $name = (string)$this->_locale->getCountryTranslation($data['value']);
            if (!empty($name)) {
                $sort[$name] = $data['value'];
            }
        }
        $this->_arrayUtils->ksortMultibyte($sort, $this->_locale->getLocaleCode());
        foreach (array_reverse($this->_foregroundCountries) as $foregroundCountry) {
            $name = array_search($foregroundCountry, $sort);
            unset($sort[$name]);
            $sort = array($name => $foregroundCountry) + $sort;
        }
        $options = array();
        foreach ($sort as $label => $value) {
            $options[] = array(
               'value' => $value,
               'label' => $label
            );
        }

        if (count($options) > 0 && $emptyLabel !== false) {
            array_unshift($options, array('value' => '', 'label' => $emptyLabel));
        }

        return $options;
    }

    /**
     * Set foreground countries array
     *
     * @param string|array $foregroundCountries
     * @return $this
     */
    public function setForegroundCountries($foregroundCountries)
    {
        if (empty($foregroundCountries)) {
            return $this;
        }
        $this->_foregroundCountries = (array)$foregroundCountries;
        return $this;
    }
}

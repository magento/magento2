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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Country collection
 */
namespace Magento\Directory\Model\Resource\Region;

class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Locale region name table name
     *
     * @var string
     */
    protected $_regionNameTable;

    /**
     * Country table name
     *
     * @var string
     */
    protected $_countryTable;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param mixed $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->_localeResolver = $localeResolver;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define main, country, locale region name tables
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Directory\Model\Region', 'Magento\Directory\Model\Resource\Region');

        $this->_countryTable = $this->getTable('directory_country');
        $this->_regionNameTable = $this->getTable('directory_country_region_name');

        $this->addOrder('name', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        $this->addOrder('default_name', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
    }

    /**
     * Initialize select object
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $locale = $this->_localeResolver->getLocaleCode();

        $this->addBindParam(':region_locale', $locale);
        $this->getSelect()->joinLeft(
            array('rname' => $this->_regionNameTable),
            'main_table.region_id = rname.region_id AND rname.locale = :region_locale',
            array('name')
        );

        return $this;
    }

    /**
     * Filter by country_id
     *
     * @param string|array $countryId
     * @return $this
     */
    public function addCountryFilter($countryId)
    {
        if (!empty($countryId)) {
            if (is_array($countryId)) {
                $this->addFieldToFilter('main_table.country_id', array('in' => $countryId));
            } else {
                $this->addFieldToFilter('main_table.country_id', $countryId);
            }
        }
        return $this;
    }

    /**
     * Filter by country code (ISO 3)
     *
     * @param string $countryCode
     * @return $this
     */
    public function addCountryCodeFilter($countryCode)
    {
        $this->getSelect()->joinLeft(
            array('country' => $this->_countryTable),
            'main_table.country_id = country.country_id'
        )->where(
            'country.iso3_code = ?',
            $countryCode
        );

        return $this;
    }

    /**
     * Filter by Region code
     *
     * @param string|array $regionCode
     * @return $this
     */
    public function addRegionCodeFilter($regionCode)
    {
        if (!empty($regionCode)) {
            if (is_array($regionCode)) {
                $this->addFieldToFilter('main_table.code', array('in' => $regionCode));
            } else {
                $this->addFieldToFilter('main_table.code', $regionCode);
            }
        }
        return $this;
    }

    /**
     * Filter by region name
     *
     * @param string|array $regionName
     * @return $this
     */
    public function addRegionNameFilter($regionName)
    {
        if (!empty($regionName)) {
            if (is_array($regionName)) {
                $this->addFieldToFilter('main_table.default_name', array('in' => $regionName));
            } else {
                $this->addFieldToFilter('main_table.default_name', $regionName);
            }
        }
        return $this;
    }

    /**
     * Filter region by its code or name
     *
     * @param string|array $region
     * @return $this
     */
    public function addRegionCodeOrNameFilter($region)
    {
        if (!empty($region)) {
            $condition = is_array($region) ? array('in' => $region) : $region;
            $this->addFieldToFilter(
                array('main_table.code', 'main_table.default_name'),
                array($condition, $condition)
            );
        }
        return $this;
    }

    /**
     * Convert collection items to select options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->_toOptionArray('region_id', 'default_name', array('title' => 'default_name'));
        if (count($options) > 0) {
            array_unshift($options, array('title ' => null, 'value' => '0', 'label' => __('--Please select--')));
        }
        return $options;
    }
}

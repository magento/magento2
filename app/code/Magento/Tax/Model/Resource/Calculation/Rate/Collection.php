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
 * Tax rate collection
 */
namespace Magento\Tax\Model\Resource\Calculation\Rate;

class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Value of fetched from DB of rules per cycle
     */
    const TAX_RULES_CHUNK_SIZE = 1000;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param mixed $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\StoreManagerInterface $storeManager,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Tax\Model\Calculation\Rate', 'Magento\Tax\Model\Resource\Calculation\Rate');
    }

    /**
     * Join country table to result
     *
     * @return $this
     */
    public function joinCountryTable()
    {
        $this->_select->join(
            array('country_table' => $this->getTable('directory_country')),
            'main_table.tax_country_id = country_table.country_id',
            array('country_name' => 'iso2_code')
        );

        return $this;
    }

    /**
     * Join Region Table
     *
     * @return $this
     */
    public function joinRegionTable()
    {
        $this->_select->joinLeft(
            array('region_table' => $this->getTable('directory_country_region')),
            'main_table.tax_region_id = region_table.region_id',
            array('region_name' => 'code')
        );
        return $this;
    }

    /**
     * Join rate title for specified store
     *
     * @param \Magento\Store\Model\Store|string|int $store
     * @return $this
     */
    public function joinTitle($store = null)
    {
        $storeId = (int)$this->_storeManager->getStore($store)->getId();
        $this->_select->joinLeft(
            array('title_table' => $this->getTable('tax_calculation_rate_title')),
            $this->getConnection()->quoteInto(
                'main_table.tax_calculation_rate_id = title_table.tax_calculation_rate_id AND title_table.store_id = ?',
                $storeId
            ),
            array('title' => 'value')
        );

        return $this;
    }

    /**
     * Joins store titles for rates
     *
     * @return $this
     */
    public function joinStoreTitles()
    {
        $storeCollection = $this->_storeManager->getStores(true);
        foreach ($storeCollection as $store) {
            $tableAlias = sprintf('title_table_%s', $store->getId());
            $joinCondition = implode(
                ' AND ',
                array(
                    "main_table.tax_calculation_rate_id = {$tableAlias}.tax_calculation_rate_id",
                    $this->getConnection()->quoteInto($tableAlias . '.store_id = ?', $store->getId())
                )
            );
            $this->_select->joinLeft(
                array($tableAlias => $this->getTable('tax_calculation_rate_title')),
                $joinCondition,
                array($tableAlias => 'value')
            );
        }
        return $this;
    }

    /**
     * Add rate filter
     *
     * @param int $rateId
     * @return $this
     */
    public function addRateFilter($rateId)
    {
        if (is_int($rateId) && $rateId > 0) {
            return $this->addFieldToFilter('main_table.tax_rate_id', $rateId);
        }

        return $this;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('tax_calculation_rate_id', 'code');
    }

    /**
     * Retrieve option hash
     *
     * @return array
     */
    public function toOptionHash()
    {
        return $this->_toOptionHash('tax_calculation_rate_id', 'code');
    }

    /**
     * Convert items array to hash for select options
     * using fetchItem method
     *
     * @see fetchItem()
     *
     * @return array
     */
    public function toOptionHashOptimized()
    {
        $result = array();
        while ($item = $this->fetchItem()) {
            $result[$item->getData('tax_calculation_rate_id')] = $item->getData('code');
        }
        return $result;
    }

    /**
     * Get rates array without memory leak
     *
     * @return array
     */
    public function getOptionRates()
    {
        $size = self::TAX_RULES_CHUNK_SIZE;
        $page = 1;
        $rates = array();
        do {
            $offset = $size * ($page - 1);
            $this->getSelect()->reset();
            $this->getSelect()
                ->from(
                    array('rates' => $this->getMainTable()),
                    array('tax_calculation_rate_id', 'code')
                )
                ->limit($size, $offset);

            $rates = array_merge($rates, $this->toOptionArray());
            $this->clear();
            $page++;
        } while ($this->getSize() > $offset);

        return $rates;
    }
}

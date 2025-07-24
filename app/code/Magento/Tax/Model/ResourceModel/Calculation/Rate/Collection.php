<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax rate collection
 */

namespace Magento\Tax\Model\ResourceModel\Calculation\Rate;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Calculation\Rate;
use Magento\Tax\Model\ResourceModel\Calculation\Rate as RateResourceModel;
use Psr\Log\LoggerInterface;

/**
 * Collection of Calculation Rates
 */
class Collection extends AbstractCollection
{
    /**
     * Value of fetched from DB of rules per cycle
     */
    const TAX_RULES_CHUNK_SIZE = 1000;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param mixed $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        ?AdapterInterface $connection = null,
        ?AbstractDb $resource = null
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
        $this->_init(Rate::class, RateResourceModel::class);
    }

    /**
     * Join country table to result
     *
     * @return $this
     */
    public function joinCountryTable()
    {
        $this->_select->join(
            ['country_table' => $this->getTable('directory_country')],
            'main_table.tax_country_id = country_table.country_id',
            ['country_name' => 'iso2_code']
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
            ['region_table' => $this->getTable('directory_country_region')],
            'main_table.tax_region_id = region_table.region_id',
            ['region_name' => 'code']
        );
        return $this;
    }

    /**
     * Join rate title for specified store
     *
     * @param Store|string|int $store
     * @return $this
     */
    public function joinTitle($store = null)
    {
        $storeId = (int)$this->_storeManager->getStore($store)->getId();
        $this->_select->joinLeft(
            ['title_table' => $this->getTable('tax_calculation_rate_title')],
            $this->getConnection()->quoteInto(
                'main_table.tax_calculation_rate_id = title_table.tax_calculation_rate_id AND title_table.store_id = ?',
                $storeId
            ),
            ['title' => 'value']
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
                [
                    "main_table.tax_calculation_rate_id = {$tableAlias}.tax_calculation_rate_id",
                    $this->getConnection()->quoteInto($tableAlias . '.store_id = ?', $store->getId())
                ]
            );
            $this->_select->joinLeft(
                [$tableAlias => $this->getTable('tax_calculation_rate_title')],
                $joinCondition,
                [$tableAlias => 'value']
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
     * Convert items array to hash for select options using fetchItem method
     *
     * @return array
     * @see fetchItem()
     */
    public function toOptionHashOptimized()
    {
        $result = [];
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
        $rates = [];
        do {
            $offset = $size * ($page - 1);
            $this->getSelect()->reset();
            $this->getSelect()
                ->from(
                    ['rates' => $this->getMainTable()],
                    ['tax_calculation_rate_id', 'code']
                )
                ->limit($size, $offset);

            $rates[] = $this->toOptionArray();
            $this->clear();
            $page++;
        } while ($this->getSize() > $offset);

        return array_merge([], ...$rates);
    }
}

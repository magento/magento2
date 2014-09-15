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
namespace Magento\Catalog\Model\Indexer\Product\Price;

class Observer
{
    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_processor;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $processor
     */
    public function __construct(
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $processor
    ) {
        $this->_storeManager = $storeManager;
        $this->_resource = $resource;
        $this->_dateTime = $dateTime;
        $this->_localeDate = $localeDate;
        $this->_eavConfig = $eavConfig;
        $this->_processor = $processor;
    }

    /**
     * Retrieve write connection instance
     *
     * @return bool|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getWriteConnection()
    {
        if (null === $this->_connection) {
            $this->_connection = $this->_resource->getConnection('write');
        }
        return $this->_connection;
    }

    /**
     * Add products to changes list with price which depends on date
     *
     * @return void
     */
    public function refreshSpecialPrices()
    {
        $connection = $this->_getWriteConnection();

        foreach ($this->_storeManager->getStores(true) as $store) {
            $timestamp = $this->_localeDate->scopeTimeStamp($store);
            $currDate = $this->_dateTime->formatDate($timestamp, false);
            $currDateExpr = $connection->quote($currDate);

            // timestamp is locale based
            if (date(\Zend_Date::HOUR_SHORT, $timestamp) == '00') {
                $format = '%Y-%m-%d %H:%i:%s';
                $this->_refreshSpecialPriceByStore(
                    $store->getId(),
                    'special_from_date',
                    $connection->getDateFormatSql($currDateExpr, $format)
                );

                $dateTo = $connection->getDateAddSql(
                    $currDateExpr,
                    -1,
                    \Magento\Framework\DB\Adapter\AdapterInterface::INTERVAL_DAY
                );
                $this->_refreshSpecialPriceByStore(
                    $store->getId(),
                    'special_to_date',
                    $connection->getDateFormatSql($dateTo, $format)
                );
            }
        }
    }

    /**
     * Reindex affected products
     *
     * @param int $storeId
     * @param string $attrCode
     * @param \Zend_Db_Expr $attrConditionValue
     * @return void
     */
    protected function _refreshSpecialPriceByStore($storeId, $attrCode, $attrConditionValue)
    {
        $attribute = $this->_eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attrCode);
        $attributeId = $attribute->getAttributeId();

        $connection = $this->_getWriteConnection();

        $select = $connection->select()->from(
            $this->_resource->getTableName(array('catalog_product_entity', 'datetime')),
            array('entity_id')
        )->where(
            'attribute_id = ?',
            $attributeId
        )->where(
            'store_id = ?',
            $storeId
        )->where(
            'value = ?',
            $attrConditionValue
        );

        $this->_processor->getIndexer()->reindexList($connection->fetchCol($select, array('entity_id')));
    }
}

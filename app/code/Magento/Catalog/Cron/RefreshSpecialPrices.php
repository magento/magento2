<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Cron;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

class RefreshSpecialPrices
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Resource
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
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ResourceConnection $resource
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $processor
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ResourceConnection $resource,
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
    protected function _getConnection()
    {
        if (null === $this->_connection) {
            $this->_connection = $this->_resource->getConnection();
        }
        return $this->_connection;
    }

    /**
     * Add products to changes list with price which depends on date
     *
     * @return void
     */
    public function execute()
    {
        $connection = $this->_getConnection();

        foreach ($this->_storeManager->getStores(true) as $store) {
            $timestamp = $this->_localeDate->scopeTimeStamp($store);
            $currDate = $this->_dateTime->formatDate($timestamp, false);
            $currDateExpr = $connection->quote($currDate);

            // timestamp is locale based
            if (date('H', $timestamp) == '00') {
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

        $linkField = $this->getMetadataPool()->getMetadata(CategoryInterface::class)->getLinkField();
        $identifierField = $this->getMetadataPool()->getMetadata(CategoryInterface::class)->getIdentifierField();

        $connection = $this->_getConnection();

        $select = $connection->select()->from(
            ['attr' => $this->_resource->getTableName(['catalog_product_entity', 'datetime'])],
            [
                $identifierField => 'cat.' . $identifierField,
            ]
        )->joinLeft(
            ['cat' => $this->_resource->getTableName('catalog_product_entity')],
            'cat.' . $linkField . '= attr.' . $linkField,
            ''
        )->where(
            'attr.attribute_id = ?',
            $attributeId
        )->where(
            'attr.store_id = ?',
            $storeId
        )->where(
            'attr.value = ?',
            $attrConditionValue
        );

        $selectData = $connection->fetchCol($select, $identifierField);

        if (!empty($selectData)) {
            $this->_processor->getIndexer()->reindexList($selectData);
        }
    }

    /**
     * Get MetadataPool instance
     * @return MetadataPool
     *
     * @deprecated
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = ObjectManager::getInstance()->get(MetadataPool::class);
        }
        return $this->metadataPool;
    }
}

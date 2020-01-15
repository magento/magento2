<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Cron;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Cron used to refresh special prices
 */
class RefreshSpecialPrices
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Resource
     */
    protected $_resource;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var Config
     */
    protected $_eavConfig;

    /**
     * @var Processor
     */
    protected $_processor;

    /**
     * @var AdapterInterface
     */
    protected $_connection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resource
     * @param DateTime $dateTime
     * @param TimezoneInterface $localeDate
     * @param Config $eavConfig
     * @param Processor $processor
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        DateTime $dateTime,
        TimezoneInterface $localeDate,
        Config $eavConfig,
        Processor $processor,
        MetadataPool $metadataPool
    ) {
        $this->_storeManager = $storeManager;
        $this->_resource = $resource;
        $this->_dateTime = $dateTime;
        $this->_localeDate = $localeDate;
        $this->_eavConfig = $eavConfig;
        $this->_processor = $processor;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Retrieve write connection instance
     *
     * @return bool|AdapterInterface
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
                    AdapterInterface::INTERVAL_DAY
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

        $linkField = $this->metadataPool->getMetadata(CategoryInterface::class)->getLinkField();
        $identifierField = $this->metadataPool->getMetadata(CategoryInterface::class)->getIdentifierField();

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
}

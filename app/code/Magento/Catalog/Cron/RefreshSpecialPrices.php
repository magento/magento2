<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Cron;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Indexer\ActionInterface;
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
    private $storeManager;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ActionInterface
     */
    private $productIndexer;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resource
     * @param DateTime $dateTime
     * @param TimezoneInterface $localeDate
     * @param Config $eavConfig
     * @param MetadataPool $metadataPool
     * @param ActionInterface $productIndexer
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        DateTime $dateTime,
        TimezoneInterface $localeDate,
        Config $eavConfig,
        MetadataPool $metadataPool,
        ActionInterface $productIndexer
    ) {
        $this->storeManager = $storeManager;
        $this->resource = $resource;
        $this->dateTime = $dateTime;
        $this->localeDate = $localeDate;
        $this->eavConfig = $eavConfig;
        $this->metadataPool = $metadataPool;
        $this->productIndexer = $productIndexer;
    }

    /**
     * Retrieve write connection instance
     *
     * @return bool|AdapterInterface
     */
    private function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = $this->resource->getConnection();
        }
        return $this->connection;
    }

    /**
     * Add products to changes list with price which depends on date
     *
     * @return void
     */
    public function execute()
    {
        $connection = $this->getConnection();

        foreach ($this->storeManager->getStores(true) as $store) {
            $timestamp = $this->localeDate->scopeTimeStamp($store);
            $currDate = $this->dateTime->formatDate($timestamp, false);
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
     *
     * @return void
     */
    protected function _refreshSpecialPriceByStore($storeId, $attrCode, $attrConditionValue)
    {
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $attrCode);
        $attributeId = $attribute->getAttributeId();

        $linkField = $this->metadataPool->getMetadata(CategoryInterface::class)->getLinkField();
        $identifierField = $this->metadataPool->getMetadata(CategoryInterface::class)->getIdentifierField();

        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['attr' => $this->resource->getTableName(['catalog_product_entity', 'datetime'])],
            [
                $identifierField => 'cat.' . $identifierField,
            ]
        )->joinLeft(
            ['cat' => $this->resource->getTableName('catalog_product_entity')],
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
            $this->productIndexer->executeList($selectData);
        }
    }
}

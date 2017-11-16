<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Attribute;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\FlagManager;

/**
 * Class WebsiteAttributesSynchronizer
 * @package Magento\Catalog\Cron
 */
class WebsiteAttributesSynchronizer
{
    const FLAG_SYNCHRONIZED = 0;
    const FLAG_SYNCHRONIZATION_IN_PROGRESS = 1;
    const FLAG_REQUIRES_SYNCHRONIZATION = 2;
    const FLAG_NAME = 'catalog_website_attribute_is_sync_required';

    const ATTRIBUTE_WEBSITE = 2;
    const GLOBAL_STORE_VIEW_ID = 0;

    const MASK_ATTRIBUTE_VALUE = '%d_%d_%d';

    /**
     * Map table names to metadata classes where link field might be found
     */
    private $tableMetaDataClass = [
        'catalog_category_entity_datetime' => CategoryInterface::class,
        'catalog_category_entity_decimal' => CategoryInterface::class,
        'catalog_category_entity_int' => CategoryInterface::class,
        'catalog_category_entity_text' => CategoryInterface::class,
        'catalog_category_entity_varchar' => CategoryInterface::class,

        'catalog_product_entity_datetime' => ProductInterface::class,
        'catalog_product_entity_decimal' => ProductInterface::class,
        'catalog_product_entity_int' => ProductInterface::class,
        'catalog_product_entity_text' => ProductInterface::class,
        'catalog_product_entity_varchar' => ProductInterface::class,
    ];

    /**
     * Internal format :
     *  [
     *    website_id => [
     *      store_view_id_1,
     *      store_view_id_2,
     *      ...
     *    ]
     *  ]
     *
     * @var array
     */
    private $groupedStoreViews = [];

    /**
     * @var array
     */
    private $processedAttributeValues = [];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var Generator
     */
    private $batchQueryGenerator;

    /**
     * @var MetadataPool
     */
    private $metaDataPool;

    /**
     * @var array
     */
    private $linkFields = [];

    /**
     * WebsiteAttributesSynchronizer constructor.
     * @param ResourceConnection $resourceConnection
     * @param FlagManager $flagManager
     * @param Generator $batchQueryGenerator,
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        FlagManager $flagManager,
        Generator $batchQueryGenerator,
        MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->connection = $this->resourceConnection->getConnection();
        $this->flagManager = $flagManager;
        $this->batchQueryGenerator = $batchQueryGenerator;
        $this->metaDataPool = $metadataPool;
    }

    /**
     * Synchronizes attribute values between different store views on website level
     * @return void
     * @throws \Exception
     */
    public function synchronize()
    {
        $this->markSynchronizationInProgress();
        $this->connection->beginTransaction();
        try {
            foreach (array_keys($this->tableMetaDataClass) as $tableName) {
                $this->synchronizeTable($tableName);
            }

            $this->markSynchronized();
            $this->connection->commit();
        } catch (\Exception $exception) {
            $this->connection->rollBack();
            $this->scheduleSynchronization();
            throw $exception;
        }
    }

    /**
     * @return bool
     */
    public function isSynchronizationRequired()
    {
        return self::FLAG_REQUIRES_SYNCHRONIZATION === $this->flagManager->getFlagData(self::FLAG_NAME);
    }

    /**
     * Puts a flag that synchronization is required
     * @return void
     */
    public function scheduleSynchronization()
    {
        $this->flagManager->saveFlag(self::FLAG_NAME, self::FLAG_REQUIRES_SYNCHRONIZATION);
    }

    /**
     * Marks flag as in progress in case if several crons enabled, so sync. won't be duplicated
     * @return void
     */
    private function markSynchronizationInProgress()
    {
        $this->flagManager->saveFlag(self::FLAG_NAME, self::FLAG_SYNCHRONIZATION_IN_PROGRESS);
    }

    /**
     * Turn off synchronization flag
     * @return void
     */
    private function markSynchronized()
    {
        $this->flagManager->saveFlag(self::FLAG_NAME, self::FLAG_SYNCHRONIZED);
    }

    /**
     * @param string $tableName
     * @return void
     */
    private function synchronizeTable($tableName)
    {
        foreach ($this->fetchAttributeValues($tableName) as $attributeValueItems) {
            $this->processAttributeValues($attributeValueItems, $tableName);
        }
    }

    /**
     * Aligns website attribute values
     * @param array $attributeValueItems
     * @param string $tableName
     * @return void
     */
    private function processAttributeValues(array $attributeValueItems, $tableName)
    {
        $this->resetProcessedAttributeValues();

        foreach ($attributeValueItems as $attributeValueItem) {
            if ($this->isAttributeValueProcessed($attributeValueItem, $tableName)) {
                continue;
            }

            $insertions = $this->generateAttributeValueInsertions($attributeValueItem, $tableName);
            if (!empty($insertions)) {
                $this->executeInsertions($insertions, $tableName);
            }

            $this->markAttributeValueProcessed($attributeValueItem, $tableName);
        }
    }

    /**
     * Yields batch of AttributeValues
     *
     * @param string $tableName
     * @yield array
     * @return void
     */
    private function fetchAttributeValues($tableName)
    {
        $batchSelectIterator = $this->batchQueryGenerator->generate(
            'value_id',
            $this->connection
                ->select()
                ->from(
                    ['cpei' => $this->resourceConnection->getTableName($tableName)],
                    '*'
                )
                ->join(
                    [
                        'cea' => $this->resourceConnection->getTableName('catalog_eav_attribute'),
                    ],
                    'cpei.attribute_id = cea.attribute_id',
                    ''
                )
                ->join(
                    [
                        'st' => $this->resourceConnection->getTableName('store'),
                    ],
                    'st.store_id = cpei.store_id',
                    'st.website_id'
                )
                ->where(
                    'cea.is_global = ?',
                    self::ATTRIBUTE_WEBSITE
                )
                ->where(
                    'cpei.store_id <> ?',
                    self::GLOBAL_STORE_VIEW_ID
                )
        );

        foreach ($batchSelectIterator as $select) {
            yield $this->connection->fetchAll($select);
        }
    }

    /**
     * @return array
     */
    private function getGroupedStoreViews()
    {
        if (!empty($this->groupedStoreViews)) {
            return $this->groupedStoreViews;
        }

        $query = $this->connection
            ->select()
            ->from(
                $this->resourceConnection->getTableName('store'),
                '*'
            );

        $storeViews = $this->connection->fetchAll($query);

        $this->groupedStoreViews = [];

        foreach ($storeViews as $storeView) {
            if ($storeView['store_id'] != 0) {
                $this->groupedStoreViews[$storeView['website_id']][] = $storeView['store_id'];
            }
        }

        return $this->groupedStoreViews;
    }

    /**
     * @param array $attributeValue
     * @param string $tableName
     * @return bool
     */
    private function isAttributeValueProcessed(array $attributeValue, $tableName)
    {
        return in_array(
            $this->getAttributeValueKey(
                $attributeValue[$this->getTableLinkField($tableName)],
                $attributeValue['attribute_id'],
                $attributeValue['website_id']
            ),
            $this->processedAttributeValues
        );
    }

    /**
     * Resets processed attribute values
     * @return void
     */
    private function resetProcessedAttributeValues()
    {
        $this->processedAttributeValues = [];
    }

    /**
     * @param array $attributeValue
     * @param string $tableName
     * @return void
     */
    private function markAttributeValueProcessed(array $attributeValue, $tableName)
    {
        $this->processedAttributeValues[] = $this->getAttributeValueKey(
            $attributeValue[$this->getTableLinkField($tableName)],
            $attributeValue['attribute_id'],
            $attributeValue['website_id']
        );
    }

    /**
     * @param int $entityId
     * @param int $attributeId
     * @param int $websiteId
     * @return string
     */
    private function getAttributeValueKey($entityId, $attributeId, $websiteId)
    {
        return sprintf(
            self::MASK_ATTRIBUTE_VALUE,
            $entityId,
            $attributeId,
            $websiteId
        );
    }

    /**
     * @param array $attributeValue
     * @param string $tableName
     * @return array|null
     */
    private function generateAttributeValueInsertions(array $attributeValue, $tableName)
    {
        $groupedStoreViews = $this->getGroupedStoreViews();
        if (empty($groupedStoreViews[$attributeValue['website_id']])) {
            return null;
        }

        $currentStoreViewIds = $groupedStoreViews[$attributeValue['website_id']];
        $insertions = [];

        foreach ($currentStoreViewIds as $index => $storeViewId) {
            $insertions[] = [
                ':attribute_id' . $index => $attributeValue['attribute_id'],
                ':store_id' . $index => $storeViewId,
                ':entity_id' . $index => $attributeValue[$this->getTableLinkField($tableName)],
                ':value' . $index => $attributeValue['value'],
            ];
        }

        return $insertions;
    }

    /**
     * @param array $insertions
     * @param string $tableName
     * @return void
     */
    private function executeInsertions(array $insertions, $tableName)
    {
        $rawQuery = sprintf(
            'INSERT INTO 
            %s(attribute_id, store_id, %s, `value`)
            VALUES 
            %s
            ON duplicate KEY UPDATE `value` = VALUES(`value`)',
            $this->resourceConnection->getTableName($tableName),
            $this->getTableLinkField($tableName),
            $this->prepareInsertValuesStatement($insertions)
        );

        $this->connection->query($rawQuery, $this->getPlaceholderValues($insertions));
    }

    /**
     * Maps $insertions hierarchy to single-level $placeholder => $value array
     *
     * @param array $insertions
     * @return array
     */
    private function getPlaceholderValues(array $insertions)
    {
        $placeholderValues = [];
        foreach ($insertions as $insertion) {
            $placeholderValues = array_merge(
                $placeholderValues,
                $insertion
            );
        }

        return $placeholderValues;
    }

    /**
     * Extracts from $insertions values placeholders and turns it into query statement view
     *
     * @param array $insertions
     * @return string
     */
    private function prepareInsertValuesStatement(array $insertions)
    {
        $statement = '';

        foreach ($insertions as $insertion) {
            $statement .= sprintf('(%s),', implode(',', array_keys($insertion)));
        }

        return rtrim($statement, ',');
    }

    /**
     * @param string $tableName
     * @return string
     * @throws LocalizedException
     */
    private function getTableLinkField($tableName)
    {
        if (!isset($this->tableMetaDataClass[$tableName])) {
            throw new LocalizedException(
                sprintf(
                    'Specified table: %s is not defined in tables list',
                    $tableName
                )
            );
        }

        if (!isset($this->linkFields[$tableName])) {
            $this->linkFields[$tableName] = $this->metaDataPool
                ->getMetadata($this->tableMetaDataClass[$tableName])
                ->getLinkField();
        }

        return $this->linkFields[$tableName];
    }
}

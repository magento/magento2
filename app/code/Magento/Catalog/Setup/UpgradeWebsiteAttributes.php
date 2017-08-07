<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class UpgradeWebsiteAttributes
 * @package Magento\Catalog\Setup
 *
 * IMPORTANT: This class const/methods can not be reused because it needs to be isolated
 */
class UpgradeWebsiteAttributes
{
    /**
     * ATTENTION: These constants must not be reused anywhere outside
     */
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
     * UpgradeWebsiteAttributes constructor.
     * @param Generator $batchQueryGenerator
     * @param MetadataPool $metadataPool
     */
    public function __construct(Generator $batchQueryGenerator, MetadataPool $metadataPool)
    {
        $this->batchQueryGenerator = $batchQueryGenerator;
        $this->metaDataPool = $metadataPool;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup)
    {
        foreach (array_keys($this->tableMetaDataClass) as $tableName) {
            $this->upgradeTable($setup, $tableName);
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param string $tableName
     * @return void
     */
    private function upgradeTable(ModuleDataSetupInterface $setup, $tableName)
    {
        foreach ($this->fetchAttributeValues($setup, $tableName) as $attributeValueItems) {
            $this->processAttributeValues($setup, $attributeValueItems, $tableName);
        }
    }

    /**
     * Aligns website attribute values
     * @param ModuleDataSetupInterface $setup
     * @param array $attributeValueItems
     * @param string $tableName
     * @return void
     */
    private function processAttributeValues(ModuleDataSetupInterface $setup, array $attributeValueItems, $tableName)
    {
        $this->resetProcessedAttributeValues();

        foreach ($attributeValueItems as $attributeValueItem) {
            if ($this->isProcessedAttributeValue($attributeValueItem, $tableName)) {
                continue;
            }

            $insertions = $this->generateAttributeValueInsertions($setup, $attributeValueItem, $tableName);
            if (!empty($insertions)) {
                $this->executeInsertions($setup, $insertions, $tableName);
            }

            $this->markAttributeValueProcessed($attributeValueItem, $tableName);
        }
    }

    /**
     * Yields batch of AttributeValues
     *
     * @param ModuleDataSetupInterface $setup
     * @param string $tableName
     * @yield array
     * @return void
     */
    private function fetchAttributeValues(ModuleDataSetupInterface $setup, $tableName)
    {
        $connection = $setup->getConnection();
        $batchSelectIterator = $this->batchQueryGenerator->generate(
            'value_id',
            $connection
                ->select()
                ->from(
                    ['cpei' => $setup->getTable($tableName)],
                    '*'
                )
                ->join(
                    [
                        'cea' => $setup->getTable('catalog_eav_attribute'),
                    ],
                    'cpei.attribute_id = cea.attribute_id',
                    ''
                )
                ->join(
                    [
                        'st' => $setup->getTable('store'),
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
            yield $connection->fetchAll($select);
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return array
     */
    private function getGroupedStoreViews(ModuleDataSetupInterface $setup)
    {
        if (!empty($this->groupedStoreViews)) {
            return $this->groupedStoreViews;
        }

        $connection = $setup->getConnection();
        $query = $connection
            ->select()
            ->from(
                $setup->getTable('store'),
                '*'
            );

        $storeViews = $connection->fetchAll($query);

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
    private function isProcessedAttributeValue(array $attributeValue, $tableName)
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
     * @param ModuleDataSetupInterface $setup
     * @param array $attributeValue
     * @param string $tableName
     * @return array|null
     */
    private function generateAttributeValueInsertions(
        ModuleDataSetupInterface $setup,
        array $attributeValue,
        $tableName
    ) {
        $groupedStoreViews = $this->getGroupedStoreViews($setup);
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
     * @param ModuleDataSetupInterface $setup
     * @param array $insertions
     * @param string $tableName
     * @return void
     */
    private function executeInsertions(ModuleDataSetupInterface $setup, array $insertions, $tableName)
    {
        $rawQuery = sprintf(
            'INSERT INTO 
            %s(attribute_id, store_id, %s, `value`)
            VALUES 
            %s
            ON duplicate KEY UPDATE `value` = VALUES(`value`)',
            $setup->getTable($tableName),
            $this->getTableLinkField($tableName),
            $this->prepareInsertValuesStatement($insertions)
        );

        $setup->getConnection()->query($rawQuery, $this->getPlaceholderValues($insertions));
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

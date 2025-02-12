<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Attribute;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\BatchRangeIteratorFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\FlagManager;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;

class WebsiteAttributesSynchronizer implements ResetAfterRequestInterface
{
    public const FLAG_SYNCHRONIZED = 0;
    public const FLAG_SYNCHRONIZATION_IN_PROGRESS = 1;
    public const FLAG_REQUIRES_SYNCHRONIZATION = 2;
    public const FLAG_NAME = 'catalog_website_attribute_is_sync_required';

    /**
     * Map table names to metadata classes where link field might be found
     *
     * @var string[]
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
     * @var MetadataPool
     */
    private $metaDataPool;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var BatchRangeIteratorFactory
     */
    private $rangeIteratorFactory;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var array
     */
    private $linkFields = [];

    /**
     * @param ResourceConnection $resourceConnection
     * @param FlagManager $flagManager
     * @param MetadataPool $metadataPool
     * @param StoreRepositoryInterface $storeRepository
     * @param BatchRangeIteratorFactory $rangeIteratorFactory
     * @param int $batchSize
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        FlagManager $flagManager,
        MetadataPool $metadataPool,
        StoreRepositoryInterface $storeRepository,
        BatchRangeIteratorFactory $rangeIteratorFactory,
        int $batchSize = 1000
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->connection = $this->resourceConnection->getConnection();
        $this->flagManager = $flagManager;
        $this->metaDataPool = $metadataPool;
        $this->storeRepository = $storeRepository;
        $this->rangeIteratorFactory = $rangeIteratorFactory;
        $this->batchSize = $batchSize;
    }

    /**
     * Synchronizes attribute values between different store views on website level
     *
     * @return void
     * @throws \Exception
     * @deprecated Synchronization should be done for the affected store only.
     * @see synchronizeStoreValues
     */
    public function synchronize()
    {
        $this->markSynchronizationInProgress();
        try {
            foreach ($this->storeRepository->getList() as $store) {
                $this->synchronizeStoreValues((int) $store->getId());
            }
            $this->markSynchronized();
        } catch (\Exception $exception) {
            $this->scheduleSynchronization();
            throw $exception;
        }
    }

    /**
     * Synchronizes website specific attribute values for provided store.
     *
     * @param int $storeId
     * @return void
     * @throws \Exception
     */
    public function synchronizeStoreValues(int $storeId): void
    {
        foreach (array_keys($this->tableMetaDataClass) as $tableName) {
            $this->synchronizeTable($tableName, $storeId);
        }
    }

    /**
     * Check if synchronization required
     *
     * @return bool
     * @deprecated Isn't used anymore.
     * @see \Magento\Catalog\Model\Attribute\Backend\WebsiteSpecific\Scheduler
     */
    public function isSynchronizationRequired(): bool
    {
        return self::FLAG_REQUIRES_SYNCHRONIZATION === $this->flagManager->getFlagData(self::FLAG_NAME);
    }

    /**
     * Puts a flag that synchronization is required
     *
     * @return void
     * @deprecated Isn't used anymore.
     * @see \Magento\Catalog\Model\Attribute\Backend\WebsiteSpecific\Scheduler
     */
    public function scheduleSynchronization()
    {
        $this->flagManager->saveFlag(self::FLAG_NAME, self::FLAG_REQUIRES_SYNCHRONIZATION);
    }

    /**
     * Marks flag as in progress in case if several crons enabled, so sync. won't be duplicated
     *
     * @return void
     * @deprecated Isn't used anymore.
     * @see \Magento\Catalog\Model\Attribute\Backend\WebsiteSpecific\Scheduler
     */
    private function markSynchronizationInProgress()
    {
        $this->flagManager->saveFlag(self::FLAG_NAME, self::FLAG_SYNCHRONIZATION_IN_PROGRESS);
    }

    /**
     * Turn off synchronization flag
     *
     * @return void
     * @deprecated Isn't used anymore.
     * @see \Magento\Catalog\Model\Attribute\Backend\WebsiteSpecific\Scheduler
     */
    private function markSynchronized()
    {
        $this->flagManager->saveFlag(self::FLAG_NAME, self::FLAG_SYNCHRONIZED);
    }

    /**
     * Perform table synchronization
     *
     * @param string $tableName
     * @param int $storeId
     * @return void
     */
    private function synchronizeTable(string $tableName, int $storeId): void
    {
        foreach ($this->fetchAttributeValues($tableName, $storeId) as $attributeValueItems) {
            if (empty($attributeValueItems)) {
                continue;
            }

            $this->processAttributeValues($attributeValueItems, $tableName, $storeId);
        }
    }

    /**
     * Aligns website attribute values
     *
     * @param array $attributeValueItems
     * @param string $tableName
     * @param int $storeId
     * @return void
     */
    private function processAttributeValues(array $attributeValueItems, string $tableName, int $storeId): void
    {
        $attributeValueItems = array_map(fn ($item) => $item + ['store_id' => $storeId], $attributeValueItems);
        $this->connection->insertOnDuplicate(
            $this->resourceConnection->getTableName($tableName),
            $attributeValueItems,
            ['value']
        );
    }

    /**
     * Yields batch of AttributeValues
     *
     * @param string $tableName
     * @param int $storeId
     * @yield array
     * @return \Generator
     */
    private function fetchAttributeValues(string $tableName, int $storeId): \Generator
    {
        $store = $this->storeRepository->getById($storeId);
        $linkField = $this->getTableLinkField($tableName);
        $select = $this->connection->select()
            ->from(
                ['cpev' => $this->resourceConnection->getTableName($tableName)],
                ['cpev.' . $linkField, 'cpev.attribute_id', 'cpev.value']
            )->joinInner(
                ['cea' => $this->resourceConnection->getTableName('catalog_eav_attribute')],
                'cea.attribute_id = cpev.attribute_id',
                []
            )->joinInner(
                ['s' => $this->resourceConnection->getTableName('store')],
                's.store_id = cpev.store_id',
                []
            )->where(
                'cea.is_global = ?',
                ScopedAttributeInterface::SCOPE_WEBSITE
            )
            ->where(
                'cpev.store_id NOT IN (?)',
                [Store::DEFAULT_STORE_ID, $storeId]
            )->where(
                's.website_id = ?',
                (int) $store->getWebsiteId()
            )->group(
                ['cpev.' . $linkField, 'cpev.attribute_id']
            );
        $batchSelectIterator = $this->rangeIteratorFactory->create(
            [
                'select' => $select,
                'batchSize' => $this->batchSize,
                'correlationName' => 'cpev',
                'rangeField' => [$linkField, 'attribute_id'],
            ]
        );

        foreach ($batchSelectIterator as $select) {
            yield $this->connection->fetchAll($select);
        }
    }

    /**
     * Retrieve table link field
     *
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

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->linkFields = [];
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogImportExport\Model\Import\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Process and saves images during import.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MediaGalleryProcessor
{
    /**
     * @var SkuProcessor
     */
    private $skuProcessor;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * DB connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var ResourceModelFactory
     */
    private $resourceFactory;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModel
     */
    private $resourceModel;

    /**
     * @var ProcessingErrorAggregatorInterface
     */
    private $errorAggregator;

    /**
     * @var string
     */
    private $productEntityLinkField;

    /**
     * @var string
     */
    private $mediaGalleryTableName;

    /**
     * @var string
     */
    private $mediaGalleryValueTableName;

    /**
     * @var string
     */
    private $mediaGalleryEntityToValueTableName;

    /**
     * @var string
     */
    private $productEntityTableName;

    /**
     * MediaProcessor constructor.
     *
     * @param SkuProcessor $skuProcessor
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param ResourceModelFactory $resourceModelFactory
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     */
    public function __construct(
        SkuProcessor $skuProcessor,
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        ResourceModelFactory $resourceModelFactory,
        ProcessingErrorAggregatorInterface $errorAggregator
    ) {
        $this->skuProcessor = $skuProcessor;
        $this->metadataPool = $metadataPool;
        $this->connection = $resourceConnection->getConnection();
        $this->resourceFactory = $resourceModelFactory;
        $this->errorAggregator = $errorAggregator;
    }

    /**
     * Save product media gallery.
     *
     * @param array $mediaGalleryData
     * @return void
     */
    public function saveMediaGallery(array $mediaGalleryData)
    {
        $this->initMediaGalleryResources();
        $mediaGalleryValues = [];
        $mediaGalleryValueData = [];
        $productMediaGalleryValueData = [];
        $mediaGalleryValueToEntityData = [];
        $mediaGalleryValueToStoreData = [];
        $productLinkIdField = $this->getProductEntityLinkField();
        foreach ($mediaGalleryData as $storeId => $storeMediaGalleryData) {
            foreach ($storeMediaGalleryData as $sku => $productMediaGalleryData) {
                $productId = $this->skuProcessor->getNewSku($sku)[$productLinkIdField];
                $productMediaGalleryValueData[$productId] = $productMediaGalleryValueData[$productId] ?? [];
                foreach ($productMediaGalleryData as $data) {
                    if (!in_array($data['value'], $productMediaGalleryValueData[$productId])) {
                        $productMediaGalleryValueData[$productId][] = $data['value'];
                        $mediaGalleryValueData[] = [
                            'attribute_id' => $data['attribute_id'],
                            'value' => $data['value'],
                        ];
                        $mediaGalleryValueToEntityData[] = [
                            'value' => $data['value'],
                            $productLinkIdField => $productId,
                        ];
                    }
                    $mediaGalleryValues[] = $data['value'];
                    $mediaGalleryValueToStoreData[] = [
                        'value' => $data['value'],
                        'store_id' => $storeId,
                        $productLinkIdField => $productId,
                        'label' => $data['label'],
                        'position' => $data['position'],
                        'disabled' => $data['disabled'],
                    ];
                }
            }
        }
        try {
            $mediaValueIdValueMap = [];
            $oldMediaValues = $this->connection->fetchCol(
                $this->connection->select()
                    ->from($this->mediaGalleryTableName, ['value_id'])
                    ->where('value IN (?)', $mediaGalleryValues)
            );
            $this->connection->insertOnDuplicate(
                $this->mediaGalleryTableName,
                $mediaGalleryValueData
            );
            $newMediaSelect = $this->connection->select()
                ->from($this->mediaGalleryTableName, ['value_id', 'value'])
                ->where('value IN (?)', $mediaGalleryValues);
            if ($oldMediaValues) {
                $newMediaSelect->where('value_id NOT IN (?)', $oldMediaValues);
            }
            $mediaValueIdValueMap = $this->connection->fetchPairs($newMediaSelect);
            $productIdMediaValueIdMap = $this->getProductIdMediaValueIdMap(
                $productMediaGalleryValueData,
                $mediaValueIdValueMap
            );
            $mediaGalleryValueToEntityData = $this->prepareMediaGalleryValueToEntityData(
                $mediaGalleryValueToEntityData,
                $productIdMediaValueIdMap
            );
            $this->connection->insertOnDuplicate(
                $this->mediaGalleryEntityToValueTableName,
                $mediaGalleryValueToEntityData,
                ['value_id']
            );
            $mediaGalleryValueToStoreData = $this->prepareMediaGalleryValueData(
                $mediaGalleryValueToStoreData,
                $productIdMediaValueIdMap
            );
            $this->connection->insertOnDuplicate(
                $this->mediaGalleryValueTableName,
                $mediaGalleryValueToStoreData,
                ['value_id', 'store_id', $productLinkIdField, 'label', 'position', 'disabled']
            );
        } catch (\Throwable $exception) {
            if ($mediaValueIdValueMap) {
                $this->connection->delete(
                    $this->mediaGalleryTableName,
                    $this->connection->quoteInto('value_id IN (?)', array_keys($mediaValueIdValueMap))
                );
            }
            throw $exception;
        }
    }

    /**
     * Get media values IDs per products IDs
     *
     * @param array $productMediaGalleryValueData
     * @param array $mediaValueIdValueMap
     * @return array
     */
    private function getProductIdMediaValueIdMap(
        array $productMediaGalleryValueData,
        array $mediaValueIdValueMap
    ): array {
        $productIdMediaValueIdMap = [];
        foreach ($productMediaGalleryValueData as $productId => $productMediaGalleryValues) {
            foreach ($productMediaGalleryValues as $productMediaGalleryValue) {
                foreach ($mediaValueIdValueMap as $valueId => $value) {
                    if ($productMediaGalleryValue === $value) {
                        $productIdMediaValueIdMap[$productId][$value] = $valueId;
                        unset($mediaValueIdValueMap[$valueId]);
                        break;
                    }
                }
            }
        }
        return $productIdMediaValueIdMap;
    }

    /**
     * Prepare media entity gallery value to entity data for insert
     *
     * @param array $mediaGalleryValueToEntityData
     * @param array $productIdMediaValueIdMap
     * @return array
     */
    private function prepareMediaGalleryValueToEntityData(
        array $mediaGalleryValueToEntityData,
        array $productIdMediaValueIdMap
    ): array {
        $productLinkIdField = $this->getProductEntityLinkField();
        foreach ($mediaGalleryValueToEntityData as $index => $data) {
            $productId = $data[$productLinkIdField];
            $value = $data['value'];
            $mediaGalleryValueToEntityData[$index]['value_id'] = $productIdMediaValueIdMap[$productId][$value];
            unset($mediaGalleryValueToEntityData[$index]['value']);
        }
        return $mediaGalleryValueToEntityData;
    }

    /**
     * Prepare media entity gallery value data for insert
     *
     * @param array $mediaGalleryValueData
     * @param array $productIdMediaValueIdMap
     * @return array
     */
    private function prepareMediaGalleryValueData(
        array $mediaGalleryValueData,
        array $productIdMediaValueIdMap
    ): array {
        $productLinkIdField = $this->getProductEntityLinkField();
        $lastPositions = $this->getLastMediaPositionPerProduct(array_keys($productIdMediaValueIdMap));
        foreach ($mediaGalleryValueData as $index => $data) {
            $productId = $data[$productLinkIdField];
            $value = $data['value'];
            $position = $data['position'];
            $storeId = $data['store_id'];
            $mediaGalleryValueData[$index]['value_id'] = $productIdMediaValueIdMap[$productId][$value];
            $mediaGalleryValueData[$index]['position'] = $position + ($lastPositions[$storeId][$productId] ?? 0);
            unset($mediaGalleryValueData[$index]['value']);
        }
        return $mediaGalleryValueData;
    }

    /**
     * Update media gallery labels.
     *
     * @param array $labels
     * @return void
     */
    public function updateMediaGalleryLabels(array $labels)
    {
        $this->updateMediaGalleryField($labels, 'label');
    }

    /**
     * Update 'disabled' field for media gallery entity
     *
     * @param array $images
     * @return void
     */
    public function updateMediaGalleryVisibility(array $images)
    {
        $this->updateMediaGalleryField($images, 'disabled');
    }

    /**
     * Update value for requested field in media gallery entities
     *
     * @param array $data
     * @param string $field
     * @return void
     */
    private function updateMediaGalleryField(array $data, $field)
    {
        $insertData = [];
        foreach ($data as $datum) {
            $imageData = $datum['imageData'];
            $exists = $datum['exists'] ?? true;

            if (!$exists) {
                $insertData[] = [
                    $field => $datum[$field],
                    $this->getProductEntityLinkField() => $imageData[$this->getProductEntityLinkField()],
                    'value_id' => $imageData['value_id'],
                    'store_id' => $imageData['store_id'],
                    'position' => $imageData['position'],
                ];
            } else {
                $this->connection->update(
                    $this->mediaGalleryValueTableName,
                    [
                        $field => $datum[$field],
                    ],
                    [
                        $this->getProductEntityLinkField() . ' = ?' => $imageData[$this->getProductEntityLinkField()],
                        'value_id = ?' => $imageData['value_id'],
                        'store_id = ?' => $imageData['store_id'],
                    ]
                );
            }
        }

        if (!empty($insertData)) {
            $this->connection->insertMultiple(
                $this->mediaGalleryValueTableName,
                $insertData
            );
        }
    }

    /**
     * Get existing images for current bunch.
     *
     * @param array $bunch
     * @return array
     */
    public function getExistingImages(array $bunch)
    {
        $result = [];
        if ($this->errorAggregator->hasToBeTerminated()) {
            return $result;
        }
        $this->initMediaGalleryResources();
        $productSKUs = array_map(
            'strval',
            array_column($bunch, Product::COL_SKU)
        );
        $select = $this->connection->select()->from(
            ['mg' => $this->mediaGalleryTableName],
            ['value' => 'mg.value']
        )->joinInner(
            ['mgvte' => $this->mediaGalleryEntityToValueTableName],
            '(mg.value_id = mgvte.value_id)',
            [
                $this->getProductEntityLinkField() => 'mgvte.' . $this->getProductEntityLinkField(),
                'value_id' => 'mgvte.value_id',
            ]
        )->joinLeft(
            ['mgv' => $this->mediaGalleryValueTableName],
            sprintf(
                '(mgv.%s = mgvte.%s AND mg.value_id = mgv.value_id)',
                $this->getProductEntityLinkField(),
                $this->getProductEntityLinkField()
            ),
            [
                'store_id' => 'mgv.store_id',
                'label' => 'mgv.label',
                'disabled' => 'mgv.disabled',
                'position' => 'mgv.position',
            ]
        )->joinInner(
            ['pe' => $this->productEntityTableName],
            "(mgvte.{$this->getProductEntityLinkField()} = pe.{$this->getProductEntityLinkField()})",
            ['sku' => 'pe.sku']
        )->where(
            'pe.sku IN (?)',
            $productSKUs
        );

        foreach ($this->connection->fetchAll($select) as $image) {
            $storeId = $image['store_id'];
            unset($image['store_id']);
            $sku = mb_strtolower($image['sku']);
            $value = ltrim($image['value'], '/\\');
            $result[$storeId][$sku][$value] = $image;
        }

        return $result;
    }

    /**
     * Init media gallery resources.
     *
     * @return void
     */
    private function initMediaGalleryResources()
    {
        if (null == $this->mediaGalleryTableName) {
            $this->productEntityTableName = $this->getResource()->getTable('catalog_product_entity');
            $this->mediaGalleryTableName = $this->getResource()->getTable('catalog_product_entity_media_gallery');
            $this->mediaGalleryValueTableName = $this->getResource()->getTable(
                'catalog_product_entity_media_gallery_value'
            );
            $this->mediaGalleryEntityToValueTableName = $this->getResource()->getTable(
                'catalog_product_entity_media_gallery_value_to_entity'
            );
        }
    }

    /**
     * Get the last media position for each product per store from the given list
     *
     * @param array $productIds
     * @return array
     */
    private function getLastMediaPositionPerProduct(array $productIds): array
    {
        $result = [];
        if ($productIds) {
            $productKeyName = $this->getProductEntityLinkField();
            // this result could be achieved by using GROUP BY. But there is no index on position column, therefore
            // it can be slower than the implementation below
            $positions = $this->connection->fetchAll(
                $this->connection
                    ->select()
                    ->from($this->mediaGalleryValueTableName, [$productKeyName, 'store_id', 'position'])
                    ->where("$productKeyName IN (?)", $productIds)
            );
            // Find the largest position for each product
            foreach ($positions as $record) {
                $productId = $record[$productKeyName];
                $storeId = $record['store_id'];
                if (!isset($result[$storeId][$productId])) {
                    $result[$storeId][$productId] = 0;
                }
                $result[$storeId][$productId] = $result[$storeId][$productId] < $record['position']
                    ? $record['position']
                    : $result[$storeId][$productId];
            }
        }

        return $result;
    }

    /**
     * Get product entity link field.
     *
     * @return string
     */
    private function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        }

        return $this->productEntityLinkField;
    }

    /**
     * Get resource.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModel
     */
    private function getResource()
    {
        if (!$this->resourceModel) {
            $this->resourceModel = $this->resourceFactory->create();
        }

        return $this->resourceModel;
    }
}

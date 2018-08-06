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
use Magento\Store\Model\Store;

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
     * @param $mediaGalleryData
     * @return void
     */
    public function saveMediaGallery(array $mediaGalleryData)
    {
        $this->initMediaGalleryResources();
        $mediaGalleryDataGlobal = array_replace_recursive(...$mediaGalleryData);
        $imageNames = [];
        $multiInsertData = [];
        $valueToProductId = [];
        foreach ($mediaGalleryDataGlobal as $productSku => $mediaGalleryRows) {
            $productId = $this->skuProcessor->getNewSku($productSku)[$this->getProductEntityLinkField()];
            $insertedGalleryImgs = [];
            foreach ($mediaGalleryRows as $insertValue) {
                if (!in_array($insertValue['value'], $insertedGalleryImgs)) {
                    $valueArr = [
                        'attribute_id' => $insertValue['attribute_id'],
                        'value' => $insertValue['value'],
                    ];
                    $valueToProductId[$insertValue['value']][] = $productId;
                    $imageNames[] = $insertValue['value'];
                    $multiInsertData[] = $valueArr;
                    $insertedGalleryImgs[] = $insertValue['value'];
                }
            }
        }
        $oldMediaValues = $this->connection->fetchAssoc(
            $this->connection->select()->from($this->mediaGalleryTableName, ['value_id', 'value'])
                ->where('value IN (?)', $imageNames)
        );
        $this->connection->insertOnDuplicate($this->mediaGalleryTableName, $multiInsertData);
        $newMediaSelect = $this->connection->select()->from($this->mediaGalleryTableName, ['value_id', 'value'])
            ->where('value IN (?)', $imageNames);
        if (array_keys($oldMediaValues)) {
            $newMediaSelect->where('value_id NOT IN (?)', array_keys($oldMediaValues));
        }
        $newMediaValues = $this->connection->fetchAssoc($newMediaSelect);
        foreach ($mediaGalleryData as $storeId => $storeMediaGalleryData) {
            $this->processMediaPerStore((int)$storeId, $storeMediaGalleryData, $newMediaValues, $valueToProductId);
        }
    }

    /**
     * Update media gallery labels.
     *
     * @param array $labels
     * @return void
     */
    public function updateMediaGalleryLabels(array $labels)
    {
        $insertData = [];
        foreach ($labels as $label) {
            $imageData = $label['imageData'];

            if ($imageData['label'] === null) {
                $insertData[] = [
                    'label' => $label['label'],
                    $this->getProductEntityLinkField() => $imageData[$this->getProductEntityLinkField()],
                    'value_id' => $imageData['value_id'],
                    'store_id' => Store::DEFAULT_STORE_ID,
                ];
            } else {
                $this->connection->update(
                    $this->mediaGalleryValueTableName,
                    [
                        'label' => $label['label'],
                    ],
                    [
                        $this->getProductEntityLinkField() . ' = ?' => $imageData[$this->getProductEntityLinkField()],
                        'value_id = ?' => $imageData['value_id'],
                        'store_id = ?' => Store::DEFAULT_STORE_ID,
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
                '(mg.value_id = mgv.value_id AND mgv.%s = mgvte.%s AND mgv.store_id = %d)',
                $this->getProductEntityLinkField(),
                $this->getProductEntityLinkField(),
                Store::DEFAULT_STORE_ID
            ),
            [
                'label' => 'mgv.label',
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
            $result[$image['sku']][$image['value']] = $image;
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
     * Save media gallery data per store.
     *
     * @param $storeId
     * @param array $mediaGalleryData
     * @param array $newMediaValues
     * @param array $valueToProductId
     * @return void
     */
    private function processMediaPerStore(
        int $storeId,
        array $mediaGalleryData,
        array $newMediaValues,
        array $valueToProductId
    ) {
        $multiInsertData = [];
        $dataForSkinnyTable = [];
        foreach ($mediaGalleryData as $mediaGalleryRows) {
            foreach ($mediaGalleryRows as $insertValue) {
                foreach ($newMediaValues as $value_id => $values) {
                    if ($values['value'] == $insertValue['value']) {
                        $insertValue['value_id'] = $value_id;
                        $insertValue[$this->getProductEntityLinkField()]
                            = array_shift($valueToProductId[$values['value']]);
                        unset($newMediaValues[$value_id]);
                        break;
                    }
                }
                if (isset($insertValue['value_id'])) {
                    $valueArr = [
                        'value_id' => $insertValue['value_id'],
                        'store_id' => $storeId,
                        $this->getProductEntityLinkField() => $insertValue[$this->getProductEntityLinkField()],
                        'label' => $insertValue['label'],
                        'position' => $insertValue['position'],
                        'disabled' => $insertValue['disabled'],
                    ];
                    $multiInsertData[] = $valueArr;
                    $dataForSkinnyTable[] = [
                        'value_id' => $insertValue['value_id'],
                        $this->getProductEntityLinkField() => $insertValue[$this->getProductEntityLinkField()],
                    ];
                }
            }
        }
        try {
            $this->connection->insertOnDuplicate(
                $this->mediaGalleryValueTableName,
                $multiInsertData,
                ['value_id', 'store_id', $this->getProductEntityLinkField(), 'label', 'position', 'disabled']
            );
            $this->connection->insertOnDuplicate(
                $this->mediaGalleryEntityToValueTableName,
                $dataForSkinnyTable,
                ['value_id']
            );
        } catch (\Exception $e) {
            $this->connection->delete(
                $this->mediaGalleryTableName,
                $this->connection->quoteInto('value_id IN (?)', $newMediaValues)
            );
        }
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

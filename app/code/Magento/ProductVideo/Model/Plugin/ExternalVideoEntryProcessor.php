<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Model\Plugin;

use Magento\Customer\Model\ResourceModel\Form\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Catalog\Model\Product;
use Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter;
use Magento\Catalog\Model\Product\Attribute\Backend\Media as MediaBackendModel;
use Magento\ProductVideo\Setup\InstallSchema;

/**
 * Class External video entry processor
 */
class ExternalVideoEntryProcessor
{
    /**
     * Key to store additional data from other stores
     */
    const ADDITIONAL_STORE_DATA_KEY = 'additional_store_data';

    /**
     * @var array
     */
    protected $videoPropertiesDbMapping = [
        'value_id' => 'value_id',
        'store_id' => 'store_id',
        'video_provider' => 'provider',
        'video_url' => 'url',
        'video_title' => 'title',
        'video_description' => 'description',
        'video_metadata' => 'metadata'
    ];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Media
     */
    protected $resourceEntryMediaGallery;

    /**
     * @var \Magento\ProductVideo\Model\ResourceModel\Video
     */
    protected $videoResourceModel;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Media $resourceEntryMediaGallery
     * @param \Magento\ProductVideo\Model\ResourceModel\Video $videoResourceModel
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Media $resourceEntryMediaGallery,
        \Magento\ProductVideo\Model\ResourceModel\Video $videoResourceModel
    ) {
        $this->resourceEntryMediaGallery = $resourceEntryMediaGallery;
        $this->videoResourceModel = $videoResourceModel;
    }

    /**
     * @param MediaBackendModel $mediaBackendModel
     * @param Product $product
     * @return Product
     */
    public function afterAfterLoad(MediaBackendModel $mediaBackendModel, Product $product)
    {
        $mediaCollection = $this->getMediaEntriesDataCollection($product, $mediaBackendModel->getAttribute());
        if (empty($mediaCollection)) {
            return $product;
        }

        $ids = $this->collectVideoEntriesIds($mediaCollection);
        if (empty($ids)) {
            return $product;
        }

        $videoDataCollection = $this->loadVideoDataById($ids, $product->getStoreId());
        $mediaEntriesDataCollection = $this->addVideoDataToMediaEntries($mediaCollection, $videoDataCollection);
        $product->setData($mediaBackendModel->getAttribute()->getAttributeCode(), $mediaEntriesDataCollection);

        return $product;
    }
    
    

    /**
     * @param MediaBackendModel $mediaBackendModel
     * @param Product $product
     * @return Product
     */
    public function afterBeforeSave(MediaBackendModel $mediaBackendModel, Product $product)
    {
        $attribute = $mediaBackendModel->getAttribute();
        $mediaCollection = $this->getMediaEntriesDataCollection($product, $attribute);
        if (!empty($mediaCollection)) {
            $storeDataCollection = $this->loadStoreViewVideoData($mediaCollection, $product->getStoreId());
            $mediaCollection = $this->addAdditionalStoreData($mediaCollection, $storeDataCollection);
            $product->setData(
                $attribute->getAttributeCode(),
                $mediaCollection + $product->getData($attribute->getAttributeCode())
            );
        }

        return $product;
    }

    /**
     * @param MediaBackendModel $mediaBackendModel
     * @param Product $product
     * @return Product
     */
    public function afterAfterSave(MediaBackendModel $mediaBackendModel, Product $product)
    {
        $mediaCollection = $this->getMediaEntriesDataCollection($product, $mediaBackendModel->getAttribute());
        if (!empty($mediaCollection)) {
            $videoDataCollection = $this->collectVideoData($mediaCollection);
            $this->saveVideoData($videoDataCollection, $product->getStoreId());
            $this->saveAdditionalStoreData($videoDataCollection);
        }

        return $product;
    }

    /**
     * @param array $videoDataCollection
     * @param int $storeId
     * @return void
     */
    protected function saveVideoData(array $videoDataCollection, $storeId)
    {
        foreach ($videoDataCollection as $item) {
            $item['store_id'] = $storeId;
            $this->saveVideoValuesItem($item);
        }
    }

    /**
     * @param array $videoDataCollection
     * @return void
     */
    protected function saveAdditionalStoreData(array $videoDataCollection)
    {
        foreach ($videoDataCollection as $mediaItem) {
            if (!empty($mediaItem[self::ADDITIONAL_STORE_DATA_KEY])) {
                foreach ($mediaItem[self::ADDITIONAL_STORE_DATA_KEY] as $additionalStoreItem) {
                    $additionalStoreItem['value_id'] = $mediaItem['value_id'];
                    $this->saveVideoValuesItem($additionalStoreItem);
                }
            }
        }
    }

    /**
     * @param array $item
     * @return void
     */
    protected function saveVideoValuesItem(array $item)
    {
        $this->videoResourceModel->insertOnDuplicate(
            $this->prepareVideoRowDataForSave($item)
        );
    }

    /**
     * @param array $mediaCollection
     * @param int $currentStoreId
     * @return array
     */
    protected function excludeCurrentStoreRecord(array $mediaCollection, $currentStoreId)
    {
        return array_filter(
            $mediaCollection,
            function ($item) use ($currentStoreId) {
                return $item['store_id'] == $currentStoreId ? false : true;
            }
        );
    }

    /**
     * @param array $rowData
     * @return array
     */
    protected function prepareVideoRowDataForSave(array $rowData)
    {
        foreach ($this->videoPropertiesDbMapping as $sourceKey => $dbKey) {
            if (array_key_exists($sourceKey, $rowData) && $sourceKey != $dbKey) {
                $rowData[$dbKey] = $rowData[$sourceKey];
                unset($rowData[$sourceKey]);
            }
        }
        $rowData = array_intersect_key($rowData, array_flip($this->videoPropertiesDbMapping));

        return $rowData;
    }

    /**
     * @param array $mediaCollection
     * @param int $excludedStore
     * @return array
     */
    protected function loadStoreViewVideoData(array $mediaCollection, $excludedStore)
    {
        $ids = $this->collectVideoEntriesIdsToAdditionalLoad($mediaCollection);
        $result = [];
        if (!empty($ids)) {
            $result = $this->resourceEntryMediaGallery->loadDataFromTableByValueId(
                InstallSchema::GALLERY_VALUE_VIDEO_TABLE,
                $ids,
                null,
                $this->videoPropertiesDbMapping
            );
            $result = $this->excludeCurrentStoreRecord($result, $excludedStore);
        }

        return $result;
    }

    /**
     * @param array $mediaCollection
     * @return array
     */
    protected function collectVideoData(array $mediaCollection)
    {
        $videoDataCollection = [];
        foreach ($mediaCollection as $item) {
            if (!empty($item['media_type'])
                && empty($item['removed'])
                && $item['media_type'] == ExternalVideoEntryConverter::MEDIA_TYPE_CODE
            ) {
                $videoData = $this->extractVideoDataFromRowData($item);
                $videoDataCollection[] = $videoData;
            }
        }

        return $videoDataCollection;
    }

    /**
     * @param array $rowData
     * @return array
     */
    protected function extractVideoDataFromRowData(array $rowData)
    {
        return array_intersect_key(
            $rowData,
            array_merge($this->videoPropertiesDbMapping, [self::ADDITIONAL_STORE_DATA_KEY => ''])
        );
    }

    /**
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return array
     */
    protected function getMediaEntriesDataCollection(Product $product, AbstractAttribute $attribute)
    {
        $attributeCode = $attribute->getAttributeCode();
        $mediaData = $product->getData($attributeCode);
        if (!empty($mediaData['images']) && is_array($mediaData['images'])) {
            return $mediaData['images'];
        }
        return [];
    }

    /**
     * @param array $mediaCollection
     * @return array
     */
    protected function collectVideoEntriesIds(array $mediaCollection)
    {
        $ids = [];
        foreach ($mediaCollection as $item) {
            if ($item['media_type'] == ExternalVideoEntryConverter::MEDIA_TYPE_CODE
                && !array_key_exists('video_url', $item)
            ) {
                $ids[] = $item['value_id'];
            }
        }
        return $ids;
    }

    /**
     * @param array $mediaCollection
     * @return array
     */
    protected function collectVideoEntriesIdsToAdditionalLoad(array $mediaCollection)
    {
        $ids = [];
        foreach ($mediaCollection as $item) {
            if (!empty($item['media_type'])
                && empty($item['removed'])
                && $item['media_type'] == ExternalVideoEntryConverter::MEDIA_TYPE_CODE
                && isset($item['save_data_from'])
            ) {
                $ids[] = $item['save_data_from'];
            }
        }
        return $ids;
    }

    /**
     * @param array $ids
     * @param int $storeId
     * @return array
     */
    protected function loadVideoDataById(array $ids, $storeId = null)
    {
        $mainTableAlias = $this->resourceEntryMediaGallery->getMainTableAlias();
        $joinConditions = $mainTableAlias.'.value_id = store_value.value_id';
        if (null !== $storeId) {
            $joinConditions = implode(
                ' AND ',
                [
                    $joinConditions,
                    'store_value.store_id = ' . $storeId
                ]
            );
        }
        $joinTable = [
            [
                ['store_value' => $this->resourceEntryMediaGallery->getTable(InstallSchema::GALLERY_VALUE_VIDEO_TABLE)],
                $joinConditions,
                $this->getVideoProperties()
            ]
        ];
        $result = $this->resourceEntryMediaGallery->loadDataFromTableByValueId(
            InstallSchema::GALLERY_VALUE_VIDEO_TABLE,
            $ids,
            \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            [
                'value_id' => 'value_id',
                'video_provider_default' => 'provider',
                'video_url_default' => 'url',
                'video_title_default' => 'title',
                'video_description_default' => 'description',
                'video_metadata_default' => 'metadata'
            ],
            $joinTable
        );
        foreach ($result as &$item) {
            $item = $this->substituteNullsWithDefaultValues($item);
        }

        return $result;
    }

    /**
     * @param array $rowData
     * @return mixed
     */
    protected function substituteNullsWithDefaultValues(array $rowData)
    {
        foreach ($this->getVideoProperties(false) as $key) {
            if (empty($rowData[$key]) && !empty($rowData[$key.'_default'])) {
                $rowData[$key] = $rowData[$key.'_default'];
            }
            unset($rowData[$key.'_default']);
        }

        return $rowData;
    }

    /**
     * @param bool $withDbMapping
     * @return array
     */
    protected function getVideoProperties($withDbMapping = true)
    {
        $properties = $this->videoPropertiesDbMapping;
        unset($properties['value_id']);
        unset($properties['store_id']);

        return $withDbMapping ? $properties : array_keys($properties);
    }

    /**
     * @param array $mediaCollection
     * @param array $data
     * @return array
     */
    protected function addVideoDataToMediaEntries(array $mediaCollection, array $data)
    {
        $data = $this->createIndexedCollection($data);
        foreach ($mediaCollection as &$mediaItem) {
            if (array_key_exists($mediaItem['value_id'], $data)) {
                $mediaItem = array_merge($mediaItem, $data[$mediaItem['value_id']]);
            }
        }

        return ['images' => $mediaCollection];
    }

    /**
     * @param array $mediaCollection
     * @param array $data
     * @return array
     */
    protected function addAdditionalStoreData(array $mediaCollection, array $data)
    {
        foreach ($mediaCollection as &$mediaItem) {
            if (!empty($mediaItem['save_data_from'])) {
                $additionalData = $this->createAdditionalStoreDataCollection($data, $mediaItem['save_data_from']);
                if (!empty($additionalData)) {
                    $mediaItem[self::ADDITIONAL_STORE_DATA_KEY] = $additionalData;
                }
            }
        }

        return ['images' => $mediaCollection];
    }

    /**
     * @param array $storeData
     * @param int $valueId
     * @return array
     */
    protected function createAdditionalStoreDataCollection(array $storeData, $valueId)
    {
        $result = [];
        foreach ($storeData as $item) {
            if ($item['value_id'] == $valueId) {
                unset($item['value_id']);
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @param array $mediaEntriesCollection
     * @param string $indexKey
     * @return array
     */
    protected function createIndexedCollection(array $mediaEntriesCollection, $indexKey = 'value_id')
    {
        $indexedCollection = [];
        foreach ($mediaEntriesCollection as $item) {
            $id = $item[$indexKey];
            unset($item[$indexKey]);
            $indexedCollection[$id] = $item;
        }

        return $indexedCollection;
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Model\Product\Attribute\Media;

use Magento\Catalog\Model\Product\Attribute\Backend\Media\AbstractMediaGalleryEntryProcessor;
use Magento\Customer\Model\Resource\Form\Attribute;
use \Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Catalog\Model\Product;

/**
 * Class ImageMediaGalleryEntryProcessor
 */
class ExternalVideoMediaGalleryEntryProcessor extends AbstractMediaGalleryEntryProcessor
{
    /**
     * Video Data Table name
     */
    const GALLERY_VALUE_VIDEO_TABLE = 'catalog_product_entity_media_gallery_value_video';

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
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function afterLoad(Product $product, AbstractAttribute $attribute)
    {
        $mediaCollection = $this->getMediaEntriesDataCollection($product, $attribute);
        if (!empty($mediaCollection)) {
            $ids = $this->collectVideoEntriesIds($mediaCollection);
            $videoDataCollection = $this->loadVideoDataById($ids, $product->getStoreId());
            $mediaEntriesDataCollection = $this->addVideoDataToMediaEntries($mediaCollection, $videoDataCollection);
            $product->setData($attribute->getAttributeCode(), $mediaEntriesDataCollection);
        }
    }

    /**
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function afterSave(Product $product, AbstractAttribute $attribute)
    {
        $mediaCollection = $this->getMediaEntriesDataCollection($product, $attribute);
        if (!empty($mediaCollection)) {
            $videoDataCollection = $this->collectVideoData($mediaCollection);
            $this->saveVideoData($videoDataCollection, $product->getStoreId());
        }
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
            $this->resourceEntryMediaGallery->updateTable(
                self::GALLERY_VALUE_VIDEO_TABLE,
                $this->prepareVideoRowDataForSave($item),
                ['value_id', 'store_id']
            );
        }
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

        return $rowData;
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
                && !$item['removed']
                && $item['media_type'] == ExternalVideoMediaEntryConverter::MEDIA_TYPE_CODE
            ) {
                $videoData = $this->extractVideoDataFromRowData($item);
                $videoDataCollection[] = $videoData;
            }
        }

        return $videoDataCollection;
    }

    /**
     * @param $rowData
     * @return array
     */
    protected function extractVideoDataFromRowData($rowData)
    {
        return array_intersect_key($rowData, $this->videoPropertiesDbMapping);
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
            if ($item['media_type'] == ExternalVideoMediaEntryConverter::MEDIA_TYPE_CODE) {
                $ids[] = $item['value_id'];
            }
        }
        return $ids;
    }

    /**
     * @param array $ids
     * @param int $storeId
     * @return array
     */
    protected function loadVideoDataById(array $ids, $storeId)
    {
        $mainTableAlias = $this->resourceEntryMediaGallery->getMainTableAlias();
        $joinTable = [
            [
                ['store_value' => $this->resourceEntryMediaGallery->getTable(self::GALLERY_VALUE_VIDEO_TABLE)],
                implode(
                    ' AND ',
                    [
                        $mainTableAlias.'.value_id = store_value.value_id',
                        'store_value.store_id = ' . $storeId
                    ]
                ),
                $this->getVideoProperties()
            ]
        ];
        $result = $this->resourceEntryMediaGallery->loadDataFromTableByValueId(
            self::GALLERY_VALUE_VIDEO_TABLE,
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
     * @param array $mediaEntriesCollection
     * @return array
     */
    protected function createIndexedCollection(array $mediaEntriesCollection)
    {
        $indexedCollection = [];
        foreach ($mediaEntriesCollection as $item) {
            $id = $item['value_id'];
            unset($item['value_id']);
            $indexedCollection[$id] = $item;
        }

        return $indexedCollection;
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Model\Product\Attribute\Media;

use Magento\Catalog\Model\Product\Attribute\Backend\Media\AbstractMediaGalleryEntryProcessor;
use \Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Catalog\Model\Product;

/**
 * Class ImageMediaGalleryEntryProcessor
 */
class ExternalVideoMediaGalleryEntryProcessor extends AbstractMediaGalleryEntryProcessor
{
    const GALLERY_VALUE_VIDEO_TABLE = 'catalog_product_entity_media_gallery_value_video';

    protected $videoPropertiesDbMapping = [
        'video_value_id' => 'value_id',
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
        $attributeCode = $attribute->getAttributeCode();
        $mediaCollection = $this->getMediaEntriesDataCollection($product->getData($attributeCode));
        if (!empty($mediaCollection)) {
            $ids = $this->collectVideoEntriesIds($mediaCollection);
            $videoDataCollection = $this->loadVideoDataById($ids);
            $mediaEntriesDataCollection = $this->addVideoDataToMediaEntries($mediaCollection, $videoDataCollection);
            $product->setData($attributeCode, $mediaEntriesDataCollection);
        }
    }

    /**
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function afterSave(Product $product, AbstractAttribute $attribute)
    {
        $attributeCode = $attribute->getAttributeCode();
        $mediaCollection = $this->getMediaEntriesDataCollection($product->getData($attributeCode));
        if (!empty($mediaCollection)) {
            $videoDataCollection = $this->collectVideoData($mediaCollection);
            $this->saveVideoData($videoDataCollection);
        }
    }

    /**
     * @param array $videoDataCollection
     */
    protected function saveVideoData(array $videoDataCollection)
    {
        foreach ($videoDataCollection as $item) {
            $this->resourceEntryMediaGallery->updateTable(
                self::GALLERY_VALUE_VIDEO_TABLE,
                $this->prepareVideoRowDataForSave($item)
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
            if (array_key_exists($sourceKey, $rowData)) {
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
            if ($item['media_type'] == ExternalVideoMediaEntryConverter::MEDIA_TYPE_CODE) {
                $videoData = $this->extractVideoDataFromRowData($item);
                $videoData['video_value_id'] = $item['value_id'];
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
        return array_intersect($rowData, array_keys($this->videoPropertiesDbMapping));
    }

    /**
     * @param array $mediaData
     * @return array
     */
    protected function getMediaEntriesDataCollection(array $mediaData)
    {
        if (is_array($mediaData['images'])) {
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
     * @return array
     */
    protected function loadVideoDataById(array $ids)
    {
        $result = $this->resourceEntryMediaGallery->loadDataFromTableByValueId(
            self::GALLERY_VALUE_VIDEO_TABLE,
            $ids,
            $this->videoPropertiesDbMapping
        );
        return $result;
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
            $id = $item['video_value_id'];
            unset($item['video_value_id']);
            $indexedCollection[$id] = $item;
        }

        return $indexedCollection;
    }
}

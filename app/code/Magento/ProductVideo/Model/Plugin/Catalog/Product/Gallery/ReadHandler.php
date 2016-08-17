<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Model\Plugin\Catalog\Product\Gallery;

use Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter;
use Magento\ProductVideo\Setup\InstallSchema;

/**
 * Plugin for catalog product gallery read handler.
 */
class ReadHandler extends AbstractHandler
{
    /**
     * @param \Magento\Catalog\Model\Product\Gallery\ReadHandler $mediaGalleryReadHandler
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     */
    public function afterExecute(
        \Magento\Catalog\Model\Product\Gallery\ReadHandler $mediaGalleryReadHandler,
        \Magento\Catalog\Model\Product $product
    ) {
        $mediaCollection = $this->getMediaEntriesDataCollection(
            $product,
            $mediaGalleryReadHandler->getAttribute()
        );

        if (empty($mediaCollection)) {
            return $product;
        }

        $ids = $this->collectVideoEntriesIds($mediaCollection);

        if (empty($ids)) {
            return $product;
        }

        $videoDataCollection = $this->loadVideoDataById($ids, $product->getStoreId());
        $mediaEntriesDataCollection = $this->addVideoDataToMediaEntries($mediaCollection, $videoDataCollection);

        $product->setData(
            $mediaGalleryReadHandler->getAttribute()->getAttributeCode(),
            $mediaEntriesDataCollection
        );

        return $product;
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
     * @param array $ids
     * @param int $storeId
     * @return array
     */
    protected function loadVideoDataById(array $ids, $storeId = null)
    {
        $mainTableAlias = $this->resourceModel->getMainTableAlias();
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
                ['store_value' => $this->resourceModel->getTable(InstallSchema::GALLERY_VALUE_VIDEO_TABLE)],
                $joinConditions,
                $this->getVideoProperties()
            ]
        ];
        $result = $this->resourceModel->loadDataFromTableByValueId(
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

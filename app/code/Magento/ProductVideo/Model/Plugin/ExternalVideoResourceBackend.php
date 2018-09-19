<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Model\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\DB\Select;

/**
 * Media Resource decorator
 */
class ExternalVideoResourceBackend
{
    /**
     * @var \Magento\ProductVideo\Model\ResourceModel\Video
     */
    protected $videoResourceModel;

    /**
     * @param \Magento\ProductVideo\Model\ResourceModel\Video $videoResourceModel
     */
    public function __construct(\Magento\ProductVideo\Model\ResourceModel\Video $videoResourceModel)
    {
        $this->videoResourceModel = $videoResourceModel;
    }

    /**
     * @param Gallery $originalResourceModel
     * @param array $valueIdMap
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDuplicate(Gallery $originalResourceModel, array $valueIdMap)
    {
        $mediaGalleryEntitiesData = $this->videoResourceModel->loadByIds(array_keys($valueIdMap));
        foreach ($mediaGalleryEntitiesData as $row) {
            $row['value_id'] = $valueIdMap[$row['value_id']];
            $this->videoResourceModel->insertOnDuplicate($row);
        }

        return $valueIdMap;
    }

    /**
     * @param Gallery $originalResourceModel
     * @param Select $select
     * @return Select
     */
    public function afterCreateBatchBaseSelect(Gallery $originalResourceModel, Select $select)
    {
        $select = $select->joinLeft(
            ['value_video' => $originalResourceModel->getTable('catalog_product_entity_media_gallery_value_video')],
            implode(
                ' AND ',
                [
                    'value.value_id = value_video.value_id',
                    'value.store_id = value_video.store_id',
                ]
            ),
            [
                'video_provider' => 'provider',
                'video_url' => 'url',
                'video_title' => 'title',
                'video_description' => 'description',
                'video_metadata' => 'metadata'
            ]
        )->joinLeft(
            [
                'default_value_video' => $originalResourceModel->getTable(
                    'catalog_product_entity_media_gallery_value_video'
                )
            ],
            implode(
                ' AND ',
                [
                    'default_value.value_id = default_value_video.value_id',
                    'default_value.store_id = default_value_video.store_id',
                ]
            ),
            [
                'video_provider_default' => 'provider',
                'video_url_default' => 'url',
                'video_title_default' => 'title',
                'video_description_default' => 'description',
                'video_metadata_default' => 'metadata',
            ]
        );

        return $select;
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Model\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Media;
use Magento\Framework\DB\Select;
use Magento\ProductVideo\Setup\InstallSchema;

/**
 * Attribute Media Resource decorator
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
     * @param Media $originalResourceModel
     * @param array $valueIdMap
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDuplicate(Media $originalResourceModel, array $valueIdMap)
    {
        $mediaGalleryEntitiesData = $this->videoResourceModel->loadByIds(array_keys($valueIdMap));
        foreach ($mediaGalleryEntitiesData as $row) {
            $row['value_id'] = $valueIdMap[$row['value_id']];
            $this->videoResourceModel->insertOnDuplicate($row);
        }

        return $valueIdMap;
    }

    /**
     * @param Media $originalResourceModel
     * @param Select $select
     * @return Select
     */
    public function afterCreateBatchBaseSelect(Media $originalResourceModel, Select $select)
    {
        $select = $select->joinLeft(
            ['value_video' => $originalResourceModel->getTable(InstallSchema::GALLERY_VALUE_VIDEO_TABLE)],
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
            ['default_value_video' => $originalResourceModel->getTable(InstallSchema::GALLERY_VALUE_VIDEO_TABLE)],
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

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
     * Plugin for after duplicate action
     *
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
     * Plugin for after create batch base select action
     *
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
            []
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
            []
        )->columns([
            'video_provider' => $originalResourceModel->getConnection()
                ->getIfNullSql('`value_video`.`provider`', '`default_value_video`.`provider`'),
            'video_url' => $originalResourceModel->getConnection()
                ->getIfNullSql('`value_video`.`url`', '`default_value_video`.`url`'),
            'video_title' => $originalResourceModel->getConnection()
                ->getIfNullSql('`value_video`.`title`', '`default_value_video`.`title`'),
            'video_description' => $originalResourceModel->getConnection()
                ->getIfNullSql('`value_video`.`description`', '`default_value_video`.`description`'),
            'video_metadata' => $originalResourceModel->getConnection()
                ->getIfNullSql('`value_video`.`metadata`', '`default_value_video`.`metadata`'),
            'video_provider_default' => 'default_value_video.provider',
            'video_url_default' => 'default_value_video.url',
            'video_title_default' => 'default_value_video.title',
            'video_description_default' => 'default_value_video.description',
            'video_metadata_default' => 'default_value_video.metadata',
        ]);

        return $select;
    }
}

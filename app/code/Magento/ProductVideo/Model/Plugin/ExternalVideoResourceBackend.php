<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Model\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Media;

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
}

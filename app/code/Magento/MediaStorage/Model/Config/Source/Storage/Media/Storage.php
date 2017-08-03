<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Generate options for media storage selection
 */
namespace Magento\MediaStorage\Model\Config\Source\Storage\Media;

/**
 * Class \Magento\MediaStorage\Model\Config\Source\Storage\Media\Storage
 *
 * @since 2.0.0
 */
class Storage implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Magento\MediaStorage\Model\File\Storage::STORAGE_MEDIA_FILE_SYSTEM,
                'label' => __('File System'),
            ],
            ['value' => \Magento\MediaStorage\Model\File\Storage::STORAGE_MEDIA_DATABASE, 'label' => __('Database')]
        ];
    }
}

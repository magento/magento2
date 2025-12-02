<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * Generate options for media storage selection
 */
namespace Magento\MediaStorage\Model\Config\Source\Storage\Media;

class Storage implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Magento\MediaStorage\Model\File\Storage::STORAGE_MEDIA_FILE_SYSTEM,
                'label' => __('File System'),
            ],
            ['value' => \Magento\MediaStorage\Model\File\Storage::STORAGE_MEDIA_DATABASE, 'label' => __('Database (Deprecated)')]
        ];
    }
}

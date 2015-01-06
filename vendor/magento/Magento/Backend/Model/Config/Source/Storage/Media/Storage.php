<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Generate options for media storage selection
 */
namespace Magento\Backend\Model\Config\Source\Storage\Media;

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
                'value' => \Magento\Core\Model\File\Storage::STORAGE_MEDIA_FILE_SYSTEM,
                'label' => __('File System'),
            ],
            ['value' => \Magento\Core\Model\File\Storage::STORAGE_MEDIA_DATABASE, 'label' => __('Database')]
        ];
    }
}

<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Gallery;

/**
 * Class \Magento\Catalog\Model\Product\Gallery\MimeTypeExtensionMap
 *
 * @since 2.0.0
 */
class MimeTypeExtensionMap
{
    /**
     * MIME type/extension map
     *
     * @var array
     * @since 2.0.0
     */
    protected $mimeTypeExtensionMap = [
        'image/jpg' => 'jpg',
        'image/jpeg' => 'jpg',
        'image/gif' => 'gif',
        'image/png' => 'png',
    ];

    /**
     * @param string $mimeType
     * @return string
     * @since 2.0.0
     */
    public function getMimeTypeExtension($mimeType)
    {
        if (isset($this->mimeTypeExtensionMap[$mimeType])) {
            return $this->mimeTypeExtensionMap[$mimeType];
        } else {
            return "";
        }
    }
}

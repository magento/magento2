<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Product\Gallery;

class MimeTypeExtensionMap
{
    /**
     * Mapping of image MIME types to file extensions.
     *
     * @var array
     */
    protected $mimeTypeExtensionMap = [
        'image/jpg' => 'jpg',
        'image/jpeg' => 'jpg',
        'image/gif' => 'gif',
        'image/png' => 'png',
    ];

    /**
     * Resolve extension from a MIME type.
     *
     * @param string $mimeType
     * @return string
     */
    public function getMimeTypeExtension($mimeType)
    {
        if ($mimeType !==null && isset($this->mimeTypeExtensionMap[$mimeType])) {
            return $this->mimeTypeExtensionMap[$mimeType];
        } else {
            return "";
        }
    }
}

<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Downloadable\Api\Data\File;

/**
 * @codeCoverageIgnore
 * @api
 * @since 100.0.2
 */
interface ContentUploaderInterface
{
    /**
     * Upload provided downloadable file content
     *
     * @param ContentInterface $fileContent
     * @param string $contentType
     * @return array
     * @throws \InvalidArgumentException
     */
    public function upload(ContentInterface $fileContent, $contentType);
}

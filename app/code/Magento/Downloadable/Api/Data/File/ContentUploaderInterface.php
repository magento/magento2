<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api\Data\File;

/**
 * @codeCoverageIgnore
 * @api
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

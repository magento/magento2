<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\Data;

interface FileContentUploaderInterface
{
    /**
     * Upload provided downloadable file content
     *
     * @param FileContent $fileContent
     * @param string $contentType
     * @return array
     * @throws \InvalidArgumentException
     */
    public function upload(FileContent $fileContent, $contentType);
}

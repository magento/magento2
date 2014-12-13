<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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

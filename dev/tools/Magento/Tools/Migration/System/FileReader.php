<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Migration\System;

class FileReader
{
    /**
     * Retrieve contents of a file
     *
     * @param string $fileName
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getContents($fileName)
    {
        if (false === file_exists($fileName)) {
            throw new \InvalidArgumentException($fileName . ' does not exist');
        }
        return file_get_contents($fileName);
    }

    /**
     * Get file list
     *
     * @param string $pattern
     * @return string[]
     */
    public function getFileList($pattern)
    {
        return glob($pattern);
    }
}

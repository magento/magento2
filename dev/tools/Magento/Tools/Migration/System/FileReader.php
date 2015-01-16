<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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

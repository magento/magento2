<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Validation\ValidationException;

/**
 * Utility for generating a unique file name
 */
class Name
{
    /**
     * Get new file name if the given name is in use
     *
     * @param string $destinationFile
     * @return string
     */
    public function getNewFileName(string $destinationFile)
    {
        $fileInfo = $this->getPathInfo($destinationFile);
        if ($this->fileExist($destinationFile)) {
            $index = 1;
            $baseName = $fileInfo['filename'] . '.' . $fileInfo['extension'];
            while ($this->fileExist($fileInfo['dirname'] . '/' . $baseName)) {
                $baseName = $fileInfo['filename'] . '_' . $index . '.' . $fileInfo['extension'];
                $index++;
            }
            $destFileName = $baseName;
        } else {
            return $fileInfo['basename'];
        }

        return $destFileName;
    }

    /**
     * Get the path information from a given file
     *
     * @param string $destinationFile
     * @return string|string[]
     */
    private function getPathInfo(string $destinationFile)
    {
        return pathinfo($destinationFile);
    }

    /**
     * Check to see if a given file exists
     *
     * @param string $destinationFile
     * @return bool
     */
    private function fileExist(string $destinationFile)
    {
        return file_exists($destinationFile);
    }
}

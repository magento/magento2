<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem\Directory\TargetDirectory;

/**
 * Utility for generating a unique file name
 */
class Name
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\TargetDirectory
     */
    private $targetDirectory;

    public function __construct(TargetDirectory $targetDirectory = null)
    {
        $this->targetDirectory = $targetDirectory ?? ObjectManager::getInstance()->get(TargetDirectory::class);
    }

    /**
     * Get new file name if the given name is in use
     *
     * @param string $destinationFile
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getNewFileName(string $destinationFile)
    {
        $fileInfo = $this->getPathInfo($destinationFile);
        if ($this->targetDirectory->getDirectoryWrite(DirectoryList::ROOT)->isExist($destinationFile)) {
            $index = 1;
            $baseName = $fileInfo['filename'] . '.' . $fileInfo['extension'];
            while ($this->targetDirectory->getDirectoryWrite(DirectoryList::ROOT)->isExist($fileInfo['dirname'] . '/' . $baseName)) {
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
}

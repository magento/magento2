<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\View\Asset\Publisher;

use Magento\Framework\Filesystem\Directory\WriteInterface;

class Copy implements PublisherInterface
{
    /**
     * Publish file
     *
     * @param WriteInterface $rootDir
     * @param WriteInterface $targetDir
     * @param $sourcePath
     * @param $destinationPath
     * @return bool
     */
    public function publishFile(
        WriteInterface $rootDir,
        WriteInterface $targetDir,
        $sourcePath,
        $destinationPath
    ) {
        return $rootDir->copyFile($sourcePath, $destinationPath, $targetDir);
    }
}

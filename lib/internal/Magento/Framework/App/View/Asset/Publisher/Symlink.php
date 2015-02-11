<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\View\Asset\Publisher;

use Magento\Framework\Filesystem\Directory\WriteInterface;

use Magento\Framework\App\View\Asset;

class Symlink extends Asset\Publisher
{
    /**
     * Publish file
     *
     * @param WriteInterface $rootDir
     * @param string $source
     * @param string $destination
     * @param WriteInterface $dir
     * @return bool
     */
    protected function publishFile($rootDir, $source, $destination, $dir)
    {
        return $rootDir->createSymlink($source, $destination, $dir);
    }
}

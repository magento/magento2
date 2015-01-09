<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

class FileIteratorFactory
{
    /**
     * Create file iterator
     *
     * @param \Magento\Framework\Filesystem\Directory\ReadInterface $readDirectory
     * @param array $paths
     * @return FileIterator
     */
    public function create(\Magento\Framework\Filesystem\Directory\ReadInterface $readDirectory, $paths)
    {
        return new \Magento\Framework\Config\FileIterator($readDirectory, $paths);
    }
}

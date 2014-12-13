<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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

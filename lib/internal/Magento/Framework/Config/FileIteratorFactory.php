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
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    private $filesystemDriver;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem\DriverInterface $filesystemDriver
     */
    public function __construct(\Magento\Framework\Filesystem\DriverInterface $filesystemDriver)
    {
        $this->filesystemDriver = $filesystemDriver;
    }

    /**
     * Create file iterator
     *
     * @param array $paths List of absolute paths
     * @return FileIterator
     */
    public function create($paths)
    {
        return new \Magento\Framework\Config\FileIterator($this->filesystemDriver, $paths);
    }
}

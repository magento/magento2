<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

class FileIteratorFactory
{
    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     */
    private $fileReadFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem\File\ReadFactory $fileReadFactory
     */
    public function __construct(\Magento\Framework\Filesystem\File\ReadFactory $fileReadFactory)
    {
        $this->fileReadFactory = $fileReadFactory;
    }

    /**
     * Create file iterator
     *
     * @param array $paths List of absolute paths
     * @return FileIterator
     */
    public function create($paths)
    {
        return new FileIterator($this->fileReadFactory, $paths);
    }
}

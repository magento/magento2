<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

/**
 * @api
 * @since 2.0.0
 */
class FileIteratorFactory
{
    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     * @since 2.0.0
     */
    private $fileReadFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem\File\ReadFactory $fileReadFactory
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create($paths)
    {
        return new FileIterator($this->fileReadFactory, $paths);
    }
}

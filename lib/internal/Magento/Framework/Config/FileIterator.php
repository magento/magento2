<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\ReadFactory;

/**
 * Class FileIterator
 * @api
 * @since 2.0.0
 */
class FileIterator implements \Iterator, \Countable
{
    /**
     * Paths
     *
     * @var array
     * @since 2.0.0
     */
    protected $paths = [];

    /**
     * Position
     *
     * @var int
     * @since 2.0.0
     */
    protected $position;

    /**
     * File read factory
     *
     * @var ReadFactory
     * @since 2.0.0
     */
    protected $fileReadFactory;

    /**
     * Constructor
     *
     * @param ReadFactory $readFactory
     * @param array $paths
     * @since 2.0.0
     */
    public function __construct(ReadFactory $readFactory, array $paths)
    {
        $this->fileReadFactory = $readFactory;
        $this->paths = $paths;
        $this->position = 0;
    }

    /**
     * Rewind
     *
     * @return void
     * @since 2.0.0
     */
    public function rewind()
    {
        reset($this->paths);
    }

    /**
     * Current
     *
     * @return string
     * @since 2.0.0
     */
    public function current()
    {
        /** @var \Magento\Framework\Filesystem\File\Read $fileRead */
        $fileRead = $this->fileReadFactory->create($this->key(), DriverPool::FILE);
        return $fileRead->readAll();
    }

    /**
     * Key
     *
     * @return mixed
     * @since 2.0.0
     */
    public function key()
    {
        return current($this->paths);
    }

    /**
     * Next
     *
     * @return void
     * @since 2.0.0
     */
    public function next()
    {
        next($this->paths);
    }

    /**
     * Valid
     *
     * @return bool
     * @since 2.0.0
     */
    public function valid()
    {
        return (bool) $this->key();
    }

    /**
     * Convert to an array
     *
     * @return array
     * @since 2.0.0
     */
    public function toArray()
    {
        $result = [];
        foreach ($this as $item) {
            $result[$this->key()] = $item;
        }
        return $result;
    }

    /**
     * Count
     *
     * @return int
     * @since 2.0.0
     */
    public function count()
    {
        return count($this->paths);
    }
}

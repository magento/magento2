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
 * @since 100.0.2
 */
class FileIterator implements \Iterator, \Countable
{
    /**
     * Paths
     *
     * @var array
     */
    protected $paths = [];

    /**
     * Position
     *
     * @var int
     */
    protected $position;

    /**
     * File read factory
     *
     * @var ReadFactory
     */
    protected $fileReadFactory;

    /**
     * Constructor
     *
     * @param ReadFactory $readFactory
     * @param array $paths
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
     */
    public function rewind()
    {
        reset($this->paths);
    }

    /**
     * Current
     *
     * @return string
     */
    public function current()
    {
        $fileRead = $this->fileReadFactory->create($this->key(), DriverPool::FILE);
        return $fileRead->readAll();
    }

    /**
     * Key
     *
     * @return mixed
     */
    public function key()
    {
        return current($this->paths);
    }

    /**
     * Next
     *
     * @return void
     */
    public function next()
    {
        next($this->paths);
    }

    /**
     * Valid
     *
     * @return bool
     */
    public function valid()
    {
        return (bool) $this->key();
    }

    /**
     * Convert to an array
     *
     * @return array
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
     */
    public function count()
    {
        return count($this->paths);
    }
}

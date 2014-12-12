<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Config;

/**
 * Class FileIterator
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
     * Read directory
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $directoryRead;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem\Directory\ReadInterface $directory
     * @param array $paths
     */
    public function __construct(\Magento\Framework\Filesystem\Directory\ReadInterface $directory, array $paths)
    {
        $this->paths = $paths;
        $this->position = 0;
        $this->directoryRead = $directory;
    }

    /**
     *Rewind
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
        return $this->directoryRead->readFile($this->key());
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

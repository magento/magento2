<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib;

/**
 * Provides methods for nested array manipulations
 */
class ArrayManager
{
    /**
     * Default node delimiter for path
     */
    const DEFAULT_PATH_DELIMITER = '/';

    /**
     * @var array
     */
    protected $parentNode;

    /**
     * @var string
     */
    protected $nodeIndex;

    /**
     * Check if node exists
     *
     * @param string $path
     * @param array $data
     * @param string $delimiter
     * @return bool
     */
    public function exists($path, array $data, $delimiter = self::DEFAULT_PATH_DELIMITER)
    {
        return $this->find($path, $data, $delimiter);
    }

    /**
     * Retrieve node
     *
     * @param string $path
     * @param array $data
     * @param null $defaultValue
     * @param string $delimiter
     * @return mixed|null
     */
    public function get($path, array $data, $defaultValue = null, $delimiter = self::DEFAULT_PATH_DELIMITER)
    {
        return $this->find($path, $data, $delimiter) ? $this->parentNode[$this->nodeIndex] : $defaultValue;
    }

    /**
     * Set value into node and return modified data
     *
     * @param string $path
     * @param array $data
     * @param mixed $value
     * @param string $delimiter
     * @return array
     */
    public function set($path, array $data, $value, $delimiter = self::DEFAULT_PATH_DELIMITER)
    {
        if ($this->find($path, $data, $delimiter, true)) {
            $this->parentNode[$this->nodeIndex] = $value;
        }

        return $data;
    }

    /**
     * Set value into existing node and return modified data
     *
     * @param string $path
     * @param array $data
     * @param mixed $value
     * @param string $delimiter
     * @return array
     */
    public function replace($path, array $data, $value, $delimiter = self::DEFAULT_PATH_DELIMITER)
    {
        if ($this->find($path, $data, $delimiter)) {
            $this->parentNode[$this->nodeIndex] = $value;
        }

        return $data;
    }

    /**
     * Move value from one location to another
     *
     * @param string $path
     * @param string $targetPath
     * @param array $data
     * @param bool $overwrite
     * @param string $delimiter
     * @return array
     */
    public function move($path, $targetPath, array $data, $overwrite = false, $delimiter = self::DEFAULT_PATH_DELIMITER)
    {
        if ($this->find($path, $data, $delimiter)) {
            $parentNode = &$this->parentNode;
            $nodeIndex = &$this->nodeIndex;

            if ((!$this->find($targetPath, $data, $delimiter) || $overwrite)
                && $this->find($targetPath, $data, $delimiter, true)
            ) {
                $this->parentNode[$this->nodeIndex] = $parentNode[$nodeIndex];
                unset($parentNode[$nodeIndex]);
            }
        }

        return $data;
    }

    /**
     * Merge value with node and return modified data
     *
     * @param string $path
     * @param array $data
     * @param array $value
     * @param string $delimiter
     * @return array
     */
    public function merge($path, array $data, array $value, $delimiter = self::DEFAULT_PATH_DELIMITER)
    {
        if ($this->find($path, $data, $delimiter) && is_array($this->parentNode[$this->nodeIndex])) {
            $this->parentNode[$this->nodeIndex] = array_replace_recursive(
                $this->parentNode[$this->nodeIndex],
                $value
            );
        }

        return $data;
    }

    /**
     * Populate nested array if possible and needed
     *
     * @param string $path
     * @param array $data
     * @param string $delimiter
     * @return array
     */
    public function populate($path, array $data, $delimiter = self::DEFAULT_PATH_DELIMITER)
    {
        $this->find($path, $data, $delimiter, true);

        return $data;
    }

    /**
     * Remove node and return modified data
     *
     * @param string $path
     * @param array $data
     * @param string $delimiter
     * @return array
     */
    public function remove($path, array $data, $delimiter = self::DEFAULT_PATH_DELIMITER)
    {
        if ($this->find($path, $data, $delimiter)) {
            unset($this->parentNode[$this->nodeIndex]);
        }

        return $data;
    }

    /**
     * Finds node in nested array and saves its index and parent node reference
     *
     * @param string $path
     * @param array $data
     * @param string $delimiter
     * @param bool $populate
     * @return bool
     */
    protected function find($path, array &$data, $delimiter, $populate = false)
    {
        if ($path === null) {
            return false;
        }

        $currentNode = &$data;
        $path = explode($delimiter, $path);

        foreach ($path as $index) {
            if (!is_array($currentNode)) {
                return false;
            }

            if (!array_key_exists($index, $currentNode)) {
                if (!$populate) {
                    return false;
                }

                $currentNode[$index] = [];
            }

            $this->nodeIndex = $index;
            $this->parentNode = &$currentNode;
            $currentNode = &$currentNode[$index];
        }

        return true;
    }

    /**
     * Retrieve slice of specified path
     *
     * @param string $path
     * @param int $offset
     * @param int|null $length
     * @param string $delimiter
     * @return string
     */
    public function slicePath($path, $offset, $length = null, $delimiter = self::DEFAULT_PATH_DELIMITER)
    {
        return implode($delimiter, array_slice(explode($delimiter, $path), $offset, $length));
    }
}

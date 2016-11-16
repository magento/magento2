<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib;

/**
 * Provides methods for nested array manipulations
 *
 * @api
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
     * @param array|string $path
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
     * @param array|string $path
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
     * @param array|string $path
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
     * @param array|string $path
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
     * @param array|string $path
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
     * @param array|string $path
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
     * @param array|string $path
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
     * @param array|string $path
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
     * @param array|string $path
     * @param array $data
     * @param string $delimiter
     * @param bool $populate
     * @return bool
     */
    protected function find($path, array &$data, $delimiter, $populate = false)
    {
        if (is_array($path)) {
            $path = implode($delimiter, $path);
        }

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
     * Get matching paths for elements with specified indexes
     *
     * @param array|mixed $indexes
     * @param array $data
     * @param string|array|null $startPath
     * @param string|array|null $internalPath
     * @param int|null $maxResults
     * @param string $delimiter
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function findPaths(
        $indexes,
        array $data,
        $startPath = null,
        $internalPath = null,
        $maxResults = null,
        $delimiter = self::DEFAULT_PATH_DELIMITER
    ) {
        $indexes = (array)$indexes;
        $startPath = is_array($startPath) ? implode($delimiter, $startPath) : $startPath;
        $internalPath = is_array($internalPath) ? implode($delimiter, $internalPath) : $internalPath;
        $data = $startPath !== null ? $this->get($startPath, $data, [], $delimiter) : $data;
        $checkList = [$startPath => ['start' => $startPath === null, 'children' => $data]];
        $paths = [];

        while ($checkList) {
            $nextCheckList = [];

            foreach ($checkList as $path => $config) {
                foreach ($config['children'] as $childIndex => $childData) {
                    $childPath = $path . (!$config['start'] ? $delimiter : '') . $childIndex;

                    if (in_array($childIndex, $indexes, true)) {
                        $paths[] = $childPath;

                        if ($maxResults !== null && count($paths) >= $maxResults) {
                            return $paths;
                        }
                    }

                    $searchData = $internalPath !== null && is_array($childData)
                        ? $this->get($internalPath, $childData, null, $delimiter)
                        : $childData;

                    if (!empty($searchData) && is_array($searchData)) {
                        $searchPath = $childPath . ($internalPath !== null ? $delimiter . $internalPath : '');
                        $nextCheckList[$searchPath] = ['start' => false, 'children' => $searchData];
                    }
                }
            }

            $checkList = $nextCheckList;
        }

        return $paths;
    }

    /**
     * Get first matching path for elements with specified indexes
     *
     * @param array|mixed $indexes
     * @param array $data
     * @param string|array|null $startPath
     * @param string|array|null $internalPath
     * @param string $delimiter
     * @return string|null
     */
    public function findPath(
        $indexes,
        array $data,
        $startPath = null,
        $internalPath = null,
        $delimiter = self::DEFAULT_PATH_DELIMITER
    ) {
        $paths = $this->findPaths($indexes, $data, $startPath, $internalPath, 1, $delimiter);

        return $paths ? reset($paths) : null;
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

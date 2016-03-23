<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System Configuration Extends Mapper
 */
namespace Magento\Config\Model\Config\Structure\Mapper;

class ExtendsMapper extends \Magento\Config\Model\Config\Structure\AbstractMapper
{
    /**
     * System configuration array
     *
     * @var array
     */
    protected $_systemConfiguration;

    /**
     * List of already extended notes (used to break circular extends)
     *
     * @var string[]
     */
    protected $_extendedNodesList = [];

    /**
     * Class that can convert relative paths from "extends" node to absolute
     *
     * @var \Magento\Config\Model\Config\Structure\Mapper\Helper\RelativePathConverter
     */
    protected $_pathConverter;

    /**
     * @param \Magento\Config\Model\Config\Structure\Mapper\Helper\RelativePathConverter $pathConverted
     */
    public function __construct(
        \Magento\Config\Model\Config\Structure\Mapper\Helper\RelativePathConverter $pathConverted
    ) {
        $this->_pathConverter = $pathConverted;
    }

    /**
     * Apply map
     *
     * @param array $data
     * @return array
     */
    public function map(array $data)
    {
        if (!isset($data['config']['system']['sections']) || !is_array($data['config']['system']['sections'])) {
            return $data;
        }

        $this->_systemConfiguration = & $data['config']['system']['sections'];

        foreach (array_keys($this->_systemConfiguration) as $nodeName) {
            $this->_traverseAndExtend($nodeName);
        }

        return $data;
    }

    /**
     * Recursively traverse through configuration and apply extends
     *
     * @param string $path
     * @return void
     */
    protected function _traverseAndExtend($path)
    {
        $node = $this->_getDataByPath($path);

        if (!is_array($node)) {
            return;
        }

        if (!empty($node['extends'])) {
            $node = $this->_extendNode($path, $node['extends']);
        }

        if (!empty($node['children'])) {
            foreach (array_keys($node['children']) as $childName) {
                $this->_traverseAndExtend($path . '/' . $childName);
            }
        }
    }

    /**
     * Get data from config by it's path
     *
     * @param string $path
     * @return array
     */
    protected function _getDataByPath($path)
    {
        $result = $this->_systemConfiguration;
        $pathParts = $this->_transformPathToKeysList($path);

        foreach ($pathParts as $part) {
            $result = isset($result[$part]) ? $result[$part] : null;
            if ($result === null) {
                return $result;
            }
        }

        return $result;
    }

    /**
     * Extend node that located under $path in configuration with configuration that is located under $extendSourceNode
     *
     * @param string $path
     * @param string $extendSourceNode
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function _extendNode($path, $extendSourceNode)
    {
        $currentNodeData = $this->_getDataByPath($path);

        if (in_array($path, $this->_extendedNodesList)) {
            return $currentNodeData;
        }

        $extendSourcePath = $this->_pathConverter->convert($path, $extendSourceNode);
        $data = $this->_getDataByPath($extendSourcePath);

        if (!$data) {
            throw new \InvalidArgumentException(
                sprintf('Invalid path in extends attribute of config/system/sections/%s node', $path)
            );
        }

        if (isset($data['extends'])) {
            $data = $this->_extendNode($extendSourcePath, $data['extends']);
        }

        $resultingData = $this->_mergeData($data, $currentNodeData);

        $this->_replaceData($path, $resultingData);
        $this->_extendedNodesList[] = $path;

        return $resultingData;
    }

    /**
     * Recursively merge two arrays (it overwrites scalars in $arr1 with appropriate scalars from $arr2
     * and merges array values)
     *
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    protected function _mergeData($arr1, $arr2)
    {
        foreach ($arr2 as $key => $value) {
            if (isset($arr1[$key]) && is_array($arr1[$key]) && is_array($value)) {
                $arr1[$key] = $this->_mergeData($arr1[$key], $value);
            } else {
                $arr1[$key] = $value;
            }
        }

        return $arr1;
    }

    /**
     * Replace data in config by path
     *
     * @param string $path
     * @param array $newData
     * @return void
     */
    protected function _replaceData($path, $newData)
    {
        $pathParts = $this->_transformPathToKeysList($path);
        $result = & $this->_systemConfiguration;

        foreach ($pathParts as $part) {
            if (!isset($result[$part])) {
                return;
            }
            $result = & $result[$part];
        }

        $result = $newData;
    }

    /**
     * Transform path to list of keys
     *
     * @param string $path
     * @return string[]
     */
    protected function _transformPathToKeysList($path)
    {
        $path = str_replace('/', '/children/', $path);
        return explode('/', $path);
    }
}

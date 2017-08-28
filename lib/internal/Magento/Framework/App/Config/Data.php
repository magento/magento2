<?php
/**
 * Configuration data container
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

/**
 * Class \Magento\Framework\App\Config\Data
 *
 */
class Data implements DataInterface
{
    /**
     * Config data
     *
     * @var array
     */
    protected $_data = [];

    /**
     * Config source data
     *
     * @var array
     */
    protected $_source = [];

    /**
     * @param MetadataProcessor $processor
     * @param array $data
     */
    public function __construct(MetadataProcessor $processor, array $data)
    {
        /** Clone the array to work around a kink in php7 that modifies the argument by reference */
        $this->_data = $processor->process($this->arrayClone($data));
        $this->_source = $data;
    }

    /**
     * @return array
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * @inheritdoc
     */
    public function getValue($path = null)
    {
        if ($path === null) {
            return $this->_data;
        }
        $keys = explode('/', $path);
        $data = $this->_data;
        foreach ($keys as $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                return null;
            }
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function setValue($path, $value)
    {
        $keys = explode('/', $path);
        $lastKey = array_pop($keys);
        $currentElement = & $this->_data;
        foreach ($keys as $key) {
            if (!isset($currentElement[$key])) {
                $currentElement[$key] = [];
            }
            $currentElement = & $currentElement[$key];
        }
        $currentElement[$lastKey] = $value;
    }

    /**
     * Copy array by value
     *
     * @param array $data
     * @return array
     */
    private function arrayClone(array $data)
    {
        $clone = [];
        foreach ($data as $key => $value) {
            $clone[$key]= $value;
        }
        return $clone;
    }
}

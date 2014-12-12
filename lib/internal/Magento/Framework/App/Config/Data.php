<?php
/**
 * Configuration data container
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App\Config;

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
        $this->_data = $processor->process($data);
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
     * Retrieve configuration value by path
     *
     * @param null|string $path
     * @return array|string
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
     * Set configuration value
     *
     * @param string $path
     * @param mixed $value
     * @return void
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
}

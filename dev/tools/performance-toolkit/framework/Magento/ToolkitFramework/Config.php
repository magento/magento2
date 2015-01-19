<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ToolkitFramework;

class Config
{
    /**
     * Configuration array
     *
     * @var array
     */
    protected $_config = [];

    /**
     * Labels for config values
     *
     * @var array
     */
    protected $_labels = [];

    /**
     * Get config instance
     *
     * @return \Magento\ToolkitFramework\Config
     */
    public static function getInstance()
    {
        static $instance;
        if (!$instance instanceof static) {
            $instance = new static();
        }
        return $instance;
    }

    /**
     * Load config from file
     *
     * @param string $filename
     * @throws \Exception
     *
     * @return void
     */
    public function loadConfig($filename)
    {
        if (!is_readable($filename)) {
            throw new \Exception("Profile configuration file `{$filename}` is not readable or does not exists.");
        }
        $this->_config = (new \Magento\Framework\Xml\Parser())->load($filename)->xmlToArray();
    }

    /**
     * Load labels
     *
     * @param string $filename
     * @throws \Exception
     *
     * @return void
     */
    public function loadLabels($filename)
    {
        if (!is_readable($filename)) {
            throw new \Exception("Labels file `{$filename}` is not readable or does not exists.");
        }
        $this->_labels = (new \Magento\Framework\Xml\Parser())->load($filename)->xmlToArray();
    }

    /**
     * Get labels array
     *
     * @return array
     */
    public function getLabels()
    {
        return isset($this->_labels['config']['labels']) ? $this->_labels['config']['labels'] : [];
    }

    /**
     * Get profile configuration value
     *
     * @param string $key
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function getValue($key, $default = null)
    {
        return isset($this->_config['config']['profile'][$key]) ? $this->_config['config']['profile'][$key] : $default;
    }
}

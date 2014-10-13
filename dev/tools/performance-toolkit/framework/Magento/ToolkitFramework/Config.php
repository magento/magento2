<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\ToolkitFramework;

class Config
{
    /**
     * Configuration array
     *
     * @var array
     */
    protected $_config = array();

    /**
     * Labels for config values
     *
     * @var array
     */
    protected $_labels = array();

    /**
     * Get config instance
     *
     * @return \Magento\ToolkitFramework\Config
     */
    public static function getInstance()
    {
        static $instance;
        if (!$instance instanceof static) {
            $instance = new static;
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
        return isset($this->_labels['config']['labels']) ? $this->_labels['config']['labels'] : array();
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

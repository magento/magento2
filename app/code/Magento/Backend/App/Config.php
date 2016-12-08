<?php
/**
 * Default application path for backend area
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Backend\App;

use Magento\Config\App\Config\Type\System;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Backend config accessor.
 */
class Config implements ConfigInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopePool
     */
    protected $_scopePool;

    /**
     * @var \Magento\Framework\App\Config
     */
    protected $appConfig;

    /**
     * @var array
     */
    private $data;

    /**
     * @param \Magento\Framework\App\Config\ScopePool $scopePool
     * @param \Magento\Framework\App\Config|null $appConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopePool $scopePool,
        \Magento\Framework\App\Config $appConfig = null
    ) {
        $this->_scopePool = $scopePool;
        $this->appConfig = $appConfig ?: ObjectManager::getInstance()->get(
            \Magento\Framework\App\Config::class
        );
    }

    /**
     * @inheritdoc
     */
    public function getValue($path)
    {
        if (isset($this->data[$path])) {
            return $this->data[$path];
        }

        $configPath = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        if ($path) {
            $configPath .= '/' . $path;
        }
        return $this->appConfig->get(System::CONFIG_TYPE, $configPath);
    }

    /**
     * @inheritdoc
     */
    public function setValue($path, $value)
    {
        $this->data[$path] = $value;
    }

    /**
     * @inheritdoc
     */
    public function isSetFlag($path)
    {
        $configPath = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        if ($path) {
            $configPath .= '/' . $path;
        }
        return (bool) $this->appConfig->get(System::CONFIG_TYPE, $configPath);
    }
}

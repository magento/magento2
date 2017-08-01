<?php
/**
 * Default application path for backend area
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\App;

use Magento\Config\App\Config\Type\System;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Backend config accessor.
 * @since 2.0.0
 */
class Config implements ConfigInterface
{
    /**
     * @var \Magento\Framework\App\Config
     * @since 2.2.0
     */
    protected $appConfig;

    /**
     * @var array
     * @since 2.2.0
     */
    private $data;

    /**
     * @param \Magento\Framework\App\Config $appConfig
     * @return void
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\App\Config $appConfig)
    {
        $this->appConfig = $appConfig;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setValue($path, $value)
    {
        $this->data[$path] = $value;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
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

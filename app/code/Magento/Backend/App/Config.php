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
 */
class Config implements ConfigInterface
{
    /**
     * @var \Magento\Framework\App\Config
     */
    protected $appConfig;

    /**
     * @var array
     */
    private $data;

    /**
     * @param \Magento\Framework\App\Config $appConfig
     * @return void
     */
    public function __construct(\Magento\Framework\App\Config $appConfig)
    {
        $this->appConfig = $appConfig;
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
        return (bool)$this->appConfig->get(System::CONFIG_TYPE, $configPath);
    }
}

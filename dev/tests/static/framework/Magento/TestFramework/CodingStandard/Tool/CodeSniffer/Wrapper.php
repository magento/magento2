<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * PHP Code Sniffer Cli wrapper
 */
namespace Magento\TestFramework\CodingStandard\Tool\CodeSniffer;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Runner;

class Wrapper extends Runner
{
    /**
     * @var array
     */
    private $settings = [];

    /**
     * Return the current version of php code sniffer
     *
     * @return string
     */
    public function version()
    {
        $version = '0.0.0';
        if (defined('\PHP_CodeSniffer\Config::VERSION')) {
            $version = Config::VERSION;
        }
        return $version;
    }

    public function init()
    {
        $this->config->extensions = $this->settings['extensions'];
        unset($this->settings['extensions']);
        $this->config->setSettings(array_replace_recursive(
            $this->config->getSettings(),
            $this->settings
        ));
        return parent::init();
    }

    /**
     * @param array $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }
}

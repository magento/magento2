<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * An ancestor class for integrity tests
 */
namespace Magento\TestFramework\TestCase;

abstract class AbstractIntegrity extends \PHPUnit\Framework\TestCase
{
    /**
     * Cached index of enabled modules
     *
     * @var array
     */
    protected $_enabledModules = null;

    /**
     * Returns array of enabled modules
     *
     * @return array
     */
    protected function _getEnabledModules()
    {
        if ($this->_enabledModules === null) {
            /** @var $helper \Magento\TestFramework\Helper\Config */
            $helper = \Magento\TestFramework\Helper\Factory::getHelper(\Magento\TestFramework\Helper\Config::class);
            $enabledModules = $helper->getEnabledModules();
            $this->_enabledModules = array_combine($enabledModules, $enabledModules);
        }
        return $this->_enabledModules;
    }

    /**
     * Checks resource file declaration - whether it is for disabled module (e.g. 'Disabled_Module::file.ext').
     *
     * @param string $file
     * @return bool
     */
    protected function _isFileForDisabledModule($file)
    {
        $enabledModules = $this->_getEnabledModules();
        if (preg_match('/^(.*)::/', $file, $matches)) {
            $module = $matches[1];
            if (!isset($enabledModules[$module])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns flat array of themes currently located in system
     *
     * @return \Magento\Theme\Model\Theme[]
     */
    protected function _getDesignThemes()
    {
        $themeItems = [];
        /** @var $themeCollection \Magento\Theme\Model\Theme\Collection */
        $themeCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Theme\Model\ResourceModel\Theme\Collection::class
        );
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        foreach ($themeCollection as $theme) {
            $themeItems[$theme->getId()] = $theme;
        }
        return $themeItems;
    }
}

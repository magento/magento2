<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\Directory\ReadFactory;

/**
 * Maps package name to full theme path, and vice versa
 */
class ThemePackageInfo
{
    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var ReadFactory
     */
    private $readDirFactory;

    /**
     * @var array
     */
    private $packageNameToFullPathMap = [];

    /**
     * Constructor
     *
     * @param ComponentRegistrar $componentRegistrar
     * @param ReadFactory $readDirFactory
     */
    public function __construct(
        ComponentRegistrar $componentRegistrar,
        ReadFactory $readDirFactory
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->readDirFactory = $readDirFactory;
    }

    /**
     * Get package name of a theme by its full theme path
     *
     * @param string $themePath
     * @return string
     * @throws \Zend_Json_Exception
     */
    public function getPackageName($themePath)
    {
        $themePath = $this->componentRegistrar->getPath(ComponentRegistrar::THEME, $themePath);
        $themeDir = $this->readDirFactory->create($themePath);
        if ($themeDir->isExist('composer.json')) {
            $rawData = [];
            $themeFile = $themeDir->readFile('composer.json');
            if ($themeFile) {
                $rawData = \Zend_Json::decode($themeFile);
            }
            return isset($rawData['name']) ? $rawData['name'] : '';
        }
        return '';
    }

    /**
     * Get full theme path by its package name
     *
     * @param string $packageName
     * @return string
     */
    public function getFullThemePath($packageName)
    {
        if (empty($this->packageNameToFullPathMap)) {
            $this->initializeMap();
        }
        return isset($this->packageNameToFullPathMap[$packageName])
            ? $this->packageNameToFullPathMap[$packageName] : '';
    }

    /**
     * Initialize package name to full theme path map
     *
     * @return void
     * @throws \Zend_Json_Exception
     */
    private function initializeMap()
    {
        $themePaths = $this->componentRegistrar->getPaths(ComponentRegistrar::THEME);
        /** @var \Magento\Theme\Model\Theme $theme */
        foreach ($themePaths as $fullThemePath => $themeDir) {
            $themeDirRead = $this->readDirFactory->create($themeDir);
            if ($themeDirRead->isExist('composer.json')) {
                $rawData = \Zend_Json::decode($themeDirRead->readFile('composer.json'));
                if (isset($rawData['name'])) {
                    $this->packageNameToFullPathMap[$rawData['name']] = $fullThemePath;
                }
            }
        }
    }
}

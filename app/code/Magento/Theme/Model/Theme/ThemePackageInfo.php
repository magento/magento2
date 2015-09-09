<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Theme\Model\Theme\Data\Collection as DataCollection;

/**
 * Maps package name to full theme path, and vice versa
 */
class ThemePackageInfo
{
    /**
     * @var DataCollection
     */
    private $collection;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var array
     */
    private $packageNameToFullPathMap = [];

    /**
     * Constructor
     *
     * @param DataCollection $collection
     * @param Filesystem $filesystem
     */
    public function __construct(DataCollection $collection, Filesystem $filesystem)
    {
        $this->collection = $collection;
        $this->filesystem = $filesystem;
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
        $themesDirRead = $this->filesystem->getDirectoryRead(DirectoryList::THEMES);
        if ($themesDirRead->isExist($themePath . '/composer.json')) {
            $rawData = [];
            $themeFile = $themesDirRead->readFile($themePath . '/composer.json');
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
        $themesDirRead = $this->filesystem->getDirectoryRead(DirectoryList::THEMES);
        $this->collection->addDefaultPattern('*');
        /** @var \Magento\Theme\Model\Theme $theme */
        foreach ($this->collection->getIterator() as $theme) {
            $fullThemePath = $theme->getFullPath();
            if ($themesDirRead->isExist($fullThemePath . '/composer.json')) {
                $rawData = \Zend_Json::decode($themesDirRead->readFile($fullThemePath . '/composer.json'));
                if (isset($rawData['name'])) {
                    $this->packageNameToFullPathMap[$rawData['name']] = $fullThemePath;
                }
            }
        }
    }
}

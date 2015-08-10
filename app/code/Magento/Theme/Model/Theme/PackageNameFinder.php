<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Finds package name of a theme
 */
class PackageNameFinder
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Get package name of a theme by its full theme path
     *
     * @param string $fullThemePath
     * @return string
     * @throws \Zend_Json_Exception
     */
    public function getPackageName($fullThemePath)
    {
        $themesDirRead = $this->filesystem->getDirectoryRead(DirectoryList::THEMES);
        if ($themesDirRead->isExist($fullThemePath . '/composer.json')) {
            $rawData = \Zend_Json::decode($themesDirRead->readFile($fullThemePath . '/composer.json'));
            return isset($rawData['name']) ? $rawData['name'] : '';
        }
        return '';
    }
}

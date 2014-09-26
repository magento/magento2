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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Setup\Module\Setup;

use Zend\Stdlib\Glob;
use Magento\Config\FileIteratorFactory;
use Magento\Config\ConfigFactory as SystemConfigFactory;

class FileResolver
{
    /**
     * File Iterator Factory
     *
     * @var FileIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * Configuration Factory
     *
     * @var SystemConfigFactory
     */
    protected $configFactory;

    /**
     * Configurations
     *
     * @var Config
     */
    protected $config;

    /**
     * Default Constructor
     *
     * @param FileIteratorFactory $iteratorFactory
     * @param SystemConfigFactory $configFactory
     */
    public function __construct(
        FileIteratorFactory $iteratorFactory,
        SystemConfigFactory $configFactory
    ) {
        $this->iteratorFactory = $iteratorFactory;
        $this->configFactory = $configFactory;
        $this->config = $this->configFactory->create();
    }

    /**
     * Get SQL setup files by pattern
     *
     * @param string $moduleName
     * @param string $fileNamePattern
     * @return array
     */
    public function getSqlSetupFiles($moduleName, $fileNamePattern = '*.php')
    {
        $paths = [];
        $modulePath = str_replace('_', '/', $moduleName);
        // Collect files by /app/code/{modulePath}/sql/*/*.php pattern
        $files = $this->getFiles($this->config->getMagentoModulePath() . $modulePath . '/sql/*/' . $fileNamePattern);
        foreach ($files as $file) {
            $paths[] = $this->getRelativePath($file);
        }

        return $paths;
    }

    /**
     * Retrieves relative path
     *
     * @param string $path
     * @return string
     */
    protected function getRelativePath($path = null)
    {
        $basePath = $this->config->getMagentoBasePath();
        if (strpos($path, $basePath) === 0
            || $basePath == $path . '/') {
            $result = substr($path, strlen($basePath));
        } else {
            $result = $path;
        }
        return $result;
    }

    /**
     * Get Files
     *
     * @param string $path
     * @return array|false
     */
    protected function getFiles($path)
    {
        return Glob::glob($this->config->getMagentoBasePath() . $path, Glob::GLOB_BRACE);
    }

    /**
     * Get Directories
     *
     * @param string $path
     * @return array|false
     */
    protected function getDirs($path)
    {
        return Glob::glob($this->config->getMagentoBasePath() . $path, Glob::GLOB_ONLYDIR);
    }

    /**
     * Get Resource Code by Module Name
     *
     * @param string $moduleName
     * @return string
     */
    public function getResourceCode($moduleName)
    {
        $sqlResources  = [];
        $dataResources = [];
        $modulePath = str_replace('_', '/', $moduleName);

        // Collect files by /app/code/{modulePath}/sql/*/ pattern
        $resourceDirs = $this->getDirs($this->config->getMagentoModulePath() . $modulePath . '/sql/*/');
        if (!empty($resourceDirs)) {
            foreach ($resourceDirs as $resourceDir) {
                $sqlResources[] = basename($resourceDir);
            }
        }

        // Collect files by /app/code/{modulePath}/sql/*/ pattern
        $resourceDirs = $this->getDirs($this->config->getMagentoModulePath() . $modulePath . '/data/*/');
        if (!empty($resourceDirs)) {
            foreach ($resourceDirs as $resourceDir) {
                $dataResources[] = basename($resourceDir);
            }
        }

        $resources = array_unique(array_merge($sqlResources, $dataResources));
        return array_shift($resources);
    }

    /**
     * Get Absolute Path
     *
     * @param string $path
     * @return string
     */
    public function getAbsolutePath($path)
    {
        return $this->config->getMagentoBasePath() . '/' . ltrim($this->fixSeparator($path), '/');
    }

    /**
     * Fixes path separator
     * Utility method.
     *
     * @param string $path
     * @return string
     */
    protected function fixSeparator($path)
    {
        return str_replace('\\', '/', $path);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Service;

use Magento\Deploy\Config\BundleConfig;
use Magento\Deploy\Package\BundleInterface;
use Magento\Deploy\Package\BundleInterfaceFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\RepositoryMap;

/**
 * Deploy bundled static files service
 *
 * Read all static files from deployed packages and generate bundles. Bundle Factory can be configured to use
 * bundle format different from RequireJS used out of the box
 */
class Bundle
{
    /**
     * Path to package subdirectory wher bundle files are located
     */
    const BUNDLE_JS_DIR = 'js/bundle';

    /**
     * Matched file extension name for JavaScript files
     */
    const ASSET_TYPE_JS = 'js';

    /**
     * Matched file extension name for template files
     */
    const ASSET_TYPE_HTML = 'html';

    /**
     * Public static directory writable interface
     *
     * @var Filesystem\Directory\WriteInterface
     */
    private $pubStaticDir;

    /**
     * Factory for Bundle object
     *
     * @see BundleInterface
     * @var BundleInterfaceFactory
     */
    private $bundleFactory;

    /**
     * Utility class for collecting files by specific pattern and location
     *
     * @var Files
     */
    private $utilityFiles;

    /**
     * Cached data about files which must be excluded from bundling
     *
     * @var array
     */
    private $excludedCache = [];

    /**
     * List of supported types of static files
     *
     * @var array
     * */
    public static $availableTypes = [
        self::ASSET_TYPE_JS,
        self::ASSET_TYPE_HTML
    ];

    /**
     * Bundle constructor
     *
     * @param Filesystem $filesystem
     * @param BundleInterfaceFactory $bundleFactory
     * @param BundleConfig $bundleConfig
     * @param Files $files
     */
    public function __construct(
        Filesystem $filesystem,
        BundleInterfaceFactory $bundleFactory,
        BundleConfig $bundleConfig,
        Files $files
    ) {
        $this->pubStaticDir = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $this->bundleFactory = $bundleFactory;
        $this->bundleConfig = $bundleConfig;
        $this->utilityFiles = $files;
    }

    /**
     * Deploy bundles for the given area, theme and locale
     *
     * @param string $area
     * @param string $theme
     * @param string $locale
     * @return void
     */
    public function deploy($area, $theme, $locale)
    {
        $bundle = $this->bundleFactory->create([
            'area' => $area,
            'theme' => $theme,
            'locale' => $locale
        ]);

        // delete all previously created bundle files
        $bundle->clear();
        $files = [];
        $mapFilePath = $area . '/' . $theme . '/' . $locale . '/' . RepositoryMap::RESULT_MAP_NAME;
        if ($this->pubStaticDir->isFile($mapFilePath)) {
            // map file is available in compact mode, so no need to scan filesystem one more time
            $resultMap = $this->pubStaticDir->readFile($mapFilePath);
            if ($resultMap) {
                $files = json_decode($resultMap, true);
            }
        } else {
            $packageDir = $this->pubStaticDir->getAbsolutePath($area . '/' . $theme . '/' . $locale);
            $files = $this->utilityFiles->getFiles([$packageDir], '*.*');
        }

        foreach ($files as $filePath => $sourcePath) {
            if (is_array($sourcePath)) {
                $filePath = str_replace(Repository::FILE_ID_SEPARATOR, '/', $filePath);
                $sourcePath = $sourcePath['area']
                    . '/' . $sourcePath['theme']
                    . '/' . $sourcePath['locale']
                    . '/' . $filePath;
            } else {
                $sourcePath = str_replace('\\', '/', $sourcePath);
                $sourcePath = $this->pubStaticDir->getRelativePath($sourcePath);
                $filePath = substr($sourcePath, strlen($area . '/' . $theme . '/' . $locale) + 1);
            }

            $contentType = pathinfo($filePath, PATHINFO_EXTENSION);
            if (!in_array($contentType, self::$availableTypes)) {
                continue;
            }

            if ($this->hasMinVersion($filePath) || $this->isExcluded($filePath, $area, $theme)) {
                continue;
            }

            $bundle->addFile($filePath, $sourcePath, $contentType);
        }
        $bundle->flush();
    }

    /**
     * Check if file is minified version or there is a minified version of the file
     *
     * @param string $filePath
     * @return bool
     */
    private function hasMinVersion($filePath)
    {
        if (in_array($filePath, $this->excludedCache)) {
            return true;
        }

        $info = pathinfo($filePath);
        if (strpos($filePath, '.min.') === true) {
            $this->excludedCache[] = str_replace(".min.{$info['extension']}", ".{$info['extension']}", $filePath);
        } else {
            $pathToMinVersion = $info['dirname'] . '/' . $info['filename'] . '.min.' . $info['extension'];
            if ($this->pubStaticDir->isExist($pathToMinVersion)) {
                $this->excludedCache[] = $filePath;
                return true;
            }
        }

        return false;
    }

    /**
     * Check if file is in exclude list
     *
     * @param string $filePath
     * @param string $area
     * @param string $theme
     * @return bool
     */
    private function isExcluded($filePath, $area, $theme)
    {
        $excludedFiles = $this->bundleConfig->getExcludedFiles($area, $theme);
        foreach ($excludedFiles as $excludedFileId) {
            $excludedFilePath = $this->prepareExcludePath($excludedFileId);
            if ($excludedFilePath === $filePath) {
                return true;
            }
        }

        $excludedDirs = $this->bundleConfig->getExcludedDirectories($area, $theme);
        foreach ($excludedDirs as $directoryId) {
            $directoryPath = $this->prepareExcludePath($directoryId);
            if (strpos($filePath, $directoryPath) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get excluded path
     *
     * @param string $path
     * @return array|bool
     */
    private function prepareExcludePath($path)
    {
        if (strpos($path, Repository::FILE_ID_SEPARATOR) > 0) {
            list($excludedModule, $excludedPath) = explode(Repository::FILE_ID_SEPARATOR, $path);
            if ($excludedModule == 'Lib') {
                return $excludedPath;
            } else {
                return $excludedModule . '/' . $excludedPath;
            }
        }
        return $path;
    }
}

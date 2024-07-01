<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Package\Processor\PostProcessor;

use Magento\Deploy\Console\DeployStaticOptions;
use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackageFile;
use Magento\Deploy\Package\Processor\ProcessorInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Url\CssResolver;
use Magento\Framework\View\Asset\Minification;

/**
 * Post-processor scans through all CSS files and correct misleading URLs
 *
 * Such URLs may pre-exist in CSS files, but can appear when file was copied from one of the ancestors,
 * so all relative URLs need to be adjusted
 */
class CssUrls implements ProcessorInterface
{
    /**
     * Static content directory writable interface
     *
     * @var Filesystem\Directory\WriteInterface
     */
    private $staticDir;

    /**
     * Helper class for static files minification related processes
     *
     * @var Minification
     */
    private $minification;

    /**
     * Deployment procedure options
     *
     * @var array
     */
    private $options = [];

    /**
     * CssUrls constructor
     *
     * @param Filesystem $filesystem
     * @param Minification $minification
     */
    public function __construct(Filesystem $filesystem, Minification $minification)
    {
        $this->staticDir = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $this->minification = $minification;
    }

    /**
     * @inheritdoc
     */
    public function process(Package $package, array $options)
    {
        $this->options = $options;
        if ($this->options[DeployStaticOptions::NO_CSS] === true) {
            return false;
        }
        $urlMap = [];
        /** @var PackageFile $file */
        foreach (array_keys($package->getMap()) as $fileId) {
            $filePath = str_replace(\Magento\Framework\View\Asset\Repository::FILE_ID_SEPARATOR, '/', $fileId);
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            if (strtolower(pathinfo($fileId, PATHINFO_EXTENSION)) == 'css') {
                $urlMap = $this->parseCss(
                    $urlMap,
                    $filePath,
                    $package->getPath(),
                    $this->staticDir->readFile(
                        $this->minification->addMinifiedSign($package->getPath() . '/' . $filePath)
                    ),
                    $package
                );
            }
        }
        $this->updateCssUrls($urlMap);
        return true;
    }

    /**
     * Collect all URLs
     *
     * @param array $urlMap
     * @param string $cssFilePath
     * @param string $packagePath
     * @param string $cssContent
     * @param Package $package
     * @return array
     * @throws NotFoundException
     */
    private function parseCss(array $urlMap, $cssFilePath, $packagePath, $cssContent, Package $package)
    {
        $cssFilePath = $this->minification->addMinifiedSign($cssFilePath);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $cssFileBasePath = pathinfo($cssFilePath, PATHINFO_DIRNAME);
        $urls = $this->getCssUrls($cssContent);
        foreach ($urls as $url) {
            if ($this->isExternalUrl($url)) {
                $urlMap[$url][] = [
                    'filePath' => $this->minification->addMinifiedSign($packagePath . '/' . $cssFilePath),
                    'replace' => $this->getValidExternalUrl($url, $package)
                ];
                continue;
            }
            $filePath = $this->getNormalizedFilePath($packagePath . '/' . $cssFileBasePath . '/' . $url);
            if ($this->staticDir->isReadable($this->minification->addMinifiedSign($filePath))) {
                continue;
            }
            $lookupFileId = $this->getNormalizedFilePath($cssFileBasePath . '/' . $url);
            /** @var PackageFile $matchedFile */
            $matchedFile = $this->getFileFromParent($lookupFileId, $package);
            if ($matchedFile) {
                $urlMap[$url][] = [
                    'filePath' => $this->minification->addMinifiedSign($packagePath . '/' . $cssFilePath),
                    'replace' => '../../../../' // base path is always of four chunks size
                        . str_repeat('../', count(explode('/', $cssFileBasePath)))
                        . $this->minification->addMinifiedSign($matchedFile->getDeployedFilePath())
                ];
            } else {
                $filePathInBase = $package->getArea() .
                    '/' . Package::BASE_THEME .
                    '/' . $package->getLocale() .
                    '/' . $lookupFileId;
                if ($this->staticDir->isReadable($this->minification->addMinifiedSign($filePathInBase))) {
                    $urlMap[$url][] = [
                        'filePath' => $this->minification->addMinifiedSign($packagePath . '/' . $cssFilePath),
                        'replace' => str_repeat('../', count(explode('/', $cssFileBasePath)) + 4)
                            . $this->minification->addMinifiedSign($filePathInBase),
                    ];
                }
            }
        }

        return $urlMap;
    }

    /**
     * Replace relative URLs in CSS files
     *
     * @param array $urlMap
     * @return void
     */
    private function updateCssUrls(array $urlMap)
    {
        foreach ($urlMap as $ref => $targetFiles) {
            foreach ($targetFiles as $matchedFileData) {
                $filePath = $matchedFileData['filePath'];
                $oldCss = $this->staticDir->readFile($filePath);
                $newCss = str_replace($ref, $matchedFileData['replace'] ?? '', $oldCss);
                if ($oldCss !== $newCss) {
                    $this->staticDir->writeFile($filePath, $newCss);
                }
            }
        }
    }

    /**
     * Parse css and return all urls
     *
     * @param string $cssContent
     * @return array
     */
    private function getCssUrls($cssContent)
    {
        $urls = [];
        preg_match_all(CssResolver::REGEX_CSS_RELATIVE_URLS, $cssContent, $matches);
        if (!empty($matches[0]) && !empty($matches[1])) {
            $urls = array_combine($matches[0], $matches[1]);
        }
        return $urls;
    }

    /**
     * Remove “..” segments from URL
     *
     * @param string $url
     * @return string
     */
    private function getNormalizedFilePath($url)
    {
        $urlParts = explode('/', $url);
        $result = [];
        if (preg_match('/{{.*}}/', $url)) {
            foreach (array_reverse($urlParts) as $index => $part) {
                if (!preg_match('/^{{.*}}$/', $part)) {
                    $result[] = $part;
                } else {
                    break;
                }
            }
            return implode('/', array_reverse($result));
        }
        $prevIndex = 0;
        foreach ($urlParts as $index => $part) {
            if ($part == '..') {
                unset($urlParts[$index]);
                unset($urlParts[$prevIndex]);
                --$prevIndex;
            } else {
                $prevIndex = $index;
            }
        }
        return implode('/', $urlParts);
    }

    /**
     * Fulfil placeholders in external URL with appropriate area, theme and locale values
     *
     * @param string $url
     * @param Package $package
     * @return string
     */
    private function getValidExternalUrl($url, Package $package)
    {
        $url = $this->minification->removeMinifiedSign($url);
        $filePath = $this->getNormalizedFilePath($url);
        if (!$this->isFileExistsInPackage($filePath, $package)) {
            /** @var PackageFile $matchedFile */
            $matchedFile = $this->getFileFromParent($filePath, $package);
            $package = $matchedFile->getPackage();
        }
        return preg_replace(
            '/(?<=}})(.*)(?=\/{{)/',
            $package->getArea() . '/' . $package->getTheme(),
            $this->minification->addMinifiedSign($url)
        );
    }

    /**
     * Find file in ancestors by the same relative path
     *
     * @param string $fileName
     * @param Package $currentPackage
     * @return PackageFile|null
     */
    private function getFileFromParent($fileName, Package $currentPackage)
    {
        /** @var Package $package */
        foreach (array_reverse($currentPackage->getParentPackages()) as $package) {
            foreach ($package->getFiles() as $file) {
                if ($file->getDeployedFileName() === $fileName) {
                    return $file;
                }
            }
        }
        return null;
    }

    /**
     * Check if URL has placeholders, used for referencing to resources with full URL
     *
     * @param string $url
     * @return bool
     */
    private function isExternalUrl($url)
    {
        return preg_match('/{{.*}}/', $url);
    }

    /**
     * Check if file of the same deployed path exists in package
     *
     * @param string $filePath
     * @param Package $package
     * @return bool
     */
    private function isFileExistsInPackage($filePath, Package $package)
    {
        /** @var PackageFile $file */
        foreach ($package->getFiles() as $file) {
            if ($file->getDeployedFileName() === $filePath) {
                return true;
            }
        }
        return false;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Package\Processor\PreProcessor;

use Magento\Deploy\Console\DeployStaticOptions;
use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackageFile;
use Magento\Deploy\Package\Processor\ProcessorInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Url\CssResolver;
use Magento\Framework\View\Asset\Minification;

/**
 * Pre-processor for speeding up deployment of CSS files
 *
 * If there is a CSS file overridden in target theme and there are files in ancestor theme where this file was imported,
 * then all such parent files must be copied into target theme.
 */
class Css implements ProcessorInterface
{
    /**
     * @var ReadInterface
     */
    private $staticDir;

    /**
     * @var Minification
     */
    private $minification;

    /**
     * CssUrls constructor
     *
     * @param Filesystem $filesystem
     * @param Minification $minification
     */
    public function __construct(Filesystem $filesystem, Minification $minification)
    {
        $this->staticDir = $filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
        $this->minification = $minification;
    }

    /**
     * CSS files map
     *
     * Collect information about CSS files which have been imported in other CSS files
     *
     * @var []
     */
    private $map = [];

    /**
     * Deployment procedure options
     *
     * @var array
     */
    private $options = [];

    /**
     * @inheritdoc
     */
    public function process(Package $package, array $options)
    {
        $this->options = $options;
        if ($this->options[DeployStaticOptions::NO_CSS] === true) {
            return false;
        }
        if ($package->getArea() !== Package::BASE_AREA && $package->getTheme() !== Package::BASE_THEME) {
            $files = $package->getParentFiles('css');
            foreach ($files as $file) {
                $packageFile = $package->getFile($file->getFileId());
                if ($packageFile && $packageFile->getPackage() === $package) {
                    continue;
                }
                if ($this->hasOverrides($file, $package)) {
                    $file = clone $file;
                    $file->setArea($package->getArea());
                    $file->setTheme($package->getTheme());
                    $file->setLocale($package->getLocale());

                    $file->setPackage($package);
                    $package->addFileToMap($file);
                }
            }
        }
        return true;
    }

    /**
     * Checks if there are imports of CSS files or images within the given CSS file
     * which exists in the current package
     *
     * @param PackageFile $parentFile
     * @param Package $package
     * @return bool
     */
    private function hasOverrides(PackageFile $parentFile, Package $package)
    {
        $this->buildMap(
            $parentFile->getPackage()->getPath(),
            $parentFile->getDeployedFileName(),
            $parentFile->getDeployedFilePath()
        );

        $parentFiles = $this->collectFileMap($parentFile->getDeployedFilePath());

        $currentPackageFiles = $package->getFiles();

        $intersections = array_intersect_key($parentFiles, $currentPackageFiles);
        if ($intersections) {
            return true;
        }

        return false;
    }

    /**
     * Build map file
     *
     * @param string $packagePath
     * @param string $filePath
     * @param string $fullPath
     * @return void
     */
    private function buildMap($packagePath, $filePath, $fullPath)
    {
        if (!isset($this->map[$fullPath])) {
            $imports = [];
            $this->map[$fullPath] = [];

            $content = $this->staticDir->readFile($this->minification->addMinifiedSign($fullPath));

            $callback = function ($matchContent) use ($packagePath, $filePath, & $imports) {
                $importRelPath = $this->normalize(pathinfo($filePath, PATHINFO_DIRNAME) . '/' . $matchContent['path']);
                $imports[$importRelPath] = $this->normalize(
                    $packagePath . '/' . pathinfo($filePath, PATHINFO_DIRNAME) . '/' . $matchContent['path']
                );
            };
            preg_replace_callback(
                \Magento\Framework\Css\PreProcessor\Instruction\Import::REPLACE_PATTERN,
                $callback,
                $content
            );

            preg_match_all(CssResolver::REGEX_CSS_RELATIVE_URLS, $content, $matches);
            if (!empty($matches[0]) && !empty($matches[1])) {
                $urls = array_combine($matches[0], $matches[1]);
                foreach ($urls as $url) {
                    $importRelPath = $this->normalize(pathinfo($filePath, PATHINFO_DIRNAME) . '/' . $url);
                    $imports[$importRelPath] = $this->normalize(
                        $packagePath . '/' . pathinfo($filePath, PATHINFO_DIRNAME) . '/' . $url
                    );
                }
            }

            $this->map[$fullPath] = $imports;

            foreach ($imports as $importRelPath => $importFullPath) {
                // only inner CSS files are concerned
                if (strtolower(pathinfo($importFullPath, PATHINFO_EXTENSION)) === 'css') {
                    $this->buildMap($packagePath, $importRelPath, $importFullPath);
                }
            }
        }
    }

    /**
     * Flatten map tree into simple array
     *
     * Original map file information structure in form of tree,
     * and to have checking of overridden files simpler we need to flatten that tree
     *
     * @param string $fileName
     * @return array
     */
    private function collectFileMap($fileName)
    {
        $result = isset($this->map[$fileName]) ? $this->map[$fileName] : [];
        foreach ($result as $path) {
            $result = array_merge($result, $this->collectFileMap($path));
        }
        return array_unique($result);
    }

    /**
     * Return normalized path
     *
     * @param string $path
     * @return string
     */
    private function normalize($path)
    {
        if (strpos($path, '/../') === false) {
            return $path;
        }
        $pathParts = explode('/', $path);
        $realPath = [];
        foreach ($pathParts as $pathPart) {
            if ($pathPart == '.') {
                continue;
            }
            if ($pathPart == '..') {
                array_pop($realPath);
                continue;
            }
            $realPath[] = $pathPart;
        }
        return implode('/', $realPath);
    }
}

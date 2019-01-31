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
use Magento\Deploy\Service\DeployStaticFile;
use Magento\Framework\View\Asset\PreProcessor\FileNameResolver;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\Css\PreProcessor\Instruction\Import;
use Magento\Framework\View\Asset\NotationResolver;
use Magento\Framework\View\Asset\Repository;

/**
 * Pre-processor for speeding up deployment of Less files
 *
 * LESS-to-CSS compilation should happen only when it is really needed. If in some package there is no LESS files
 * overridden which used for generating some CSS file, then such CSS file can be copied from ancestor package as is
 */
class Less implements ProcessorInterface
{
    /**
     * Resolver allows to distinguish "root" LESS files from "imported" LESS files
     *
     * @var FileNameResolver
     */
    private $fileNameResolver;

    /**
     * @var \Magento\Framework\View\Asset\NotationResolver\Module
     */
    private $notationResolver;

    /**
     * @var DeployStaticFile
     */
    private $deployStaticFile;

    /**
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
     * @var array
     */
    private $map = [];

    /**
     * Less constructor
     *
     * @param FileNameResolver $fileNameResolver
     * @param NotationResolver\Module $notationResolver
     * @param DeployStaticFile $deployStaticFile
     * @param Minification $minification
     */
    public function __construct(
        FileNameResolver $fileNameResolver,
        NotationResolver\Module $notationResolver,
        DeployStaticFile $deployStaticFile,
        Minification $minification
    ) {
        $this->fileNameResolver = $fileNameResolver;
        $this->notationResolver = $notationResolver;
        $this->deployStaticFile = $deployStaticFile;
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
        if ($package->getArea() !== Package::BASE_AREA && $package->getTheme() !== Package::BASE_THEME) {
            $files = $package->getParentFiles('less');
            foreach ($files as $file) {
                $packageFile = $package->getFile($file->getFileId());
                if ($packageFile && $packageFile->getOrigPackage() === $package) {
                    continue;
                }
                $deployFileName = $this->fileNameResolver->resolve($file->getFileName());
                if ($deployFileName !== $file->getFileName()) {
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
        }
        return true;
    }

    /**
     * Checks if there are LESS files in current package which used for generating given CSS file from parent package
     *
     * If true then such CSS file must be re-compiled for current package to use overridden LESS files
     *
     * @param PackageFile $parentFile
     * @param Package $package
     * @return bool
     */
    private function hasOverrides(PackageFile $parentFile, Package $package)
    {
        $map = $this->buildMap(
            $parentFile->getFilePath(),
            $parentFile->getPackage()->getPath(),
            $parentFile->getExtension()
        );
        /** @var PackageFile[] $currentPackageFiles */
        $currentPackageFiles = array_merge($package->getFilesByType('less'), $package->getFilesByType('css'));

        foreach ($currentPackageFiles as $file) {
            if ($this->inParentFiles($file->getDeployedFileName(), $parentFile->getFileName(), $map)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string  $fileName
     * @param string  $parentFile
     * @param array $map
     * @return bool
     */
    private function inParentFiles($fileName, $parentFile, $map)
    {
        if (isset($map[$parentFile])) {
            if (in_array($fileName, $map[$parentFile])) {
                return true;
            } else {
                foreach ($map[$parentFile] as $pFile) {
                    return $this->inParentFiles($fileName, $pFile, $map);
                }
            }
        }
        return false;
    }

    /**
     * Build map of imported files
     *
     * @param string $filePath
     * @param string $packagePath
     * @param string $contentType
     * @return array
     */
    private function buildMap($filePath, $packagePath, $contentType)
    {
        $content = $this->deployStaticFile->readTmpFile($filePath, $packagePath);
        $replaceCallback = function ($matchedContent) use ($filePath, $packagePath, $contentType) {
            $matchedFileId = $matchedContent['path'];
            if (!pathinfo($matchedContent['path'], PATHINFO_EXTENSION)) {
                $matchedFileId .= '.' . $contentType;
            }
            if (strpos($matchedFileId, Repository::FILE_ID_SEPARATOR) !== false) {
                $basePath = $packagePath;
            } else {
                $basePath = pathinfo($filePath, PATHINFO_DIRNAME);
            }
            $resolvedPath = str_replace(Repository::FILE_ID_SEPARATOR, '/', $matchedFileId);
            if (strpos($resolvedPath, '@{baseUrl}') === 0) {
                $resolvedMapPath = str_replace('@{baseUrl}', '', $resolvedPath);
            } else {
                $resolvedMapPath = $this->normalizePath($basePath . '/' . $resolvedPath);
            }
            if (!isset($this->map[$filePath])) {
                $this->map[$filePath] = [];
            }
            $this->map[$filePath][] = $resolvedMapPath;
            $this->buildMap($resolvedMapPath, $packagePath, $contentType);
        };
        if ($content) {
            preg_replace_callback(Import::REPLACE_PATTERN, $replaceCallback, $content);
        }
        return $this->map;
    }

    /**
     * Return normalized path
     *
     * @param string $path
     * @return string
     */
    private function normalizePath($path)
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

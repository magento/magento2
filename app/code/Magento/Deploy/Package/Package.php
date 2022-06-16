<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Package;

use Magento\Deploy\Package\Processor\ProcessorInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\Asset\PreProcessor\FileNameResolver;

/**
 * Deployment Package
 *
 * @api
 */
class Package
{
    /**
     * @var PackagePool
     */
    private $packagePool;

    /**
     * @var FileNameResolver
     */
    private $fileNameResolver;

    /**
     * @var string
     */
    private $area;

    /**
     * @var string
     */
    private $theme;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $isVirtual;

    /**
     * @var ProcessorInterface[]
     */
    private $preProcessors;

    /**
     * @var ProcessorInterface[]
     */
    private $postProcessors;

    /**
     * @var PackageFile[]
     */
    private $files = [];

    /**
     * @var array
     */
    private $map = [];

    /**
     * @var Package
     */
    private $parent;

    /**
     * @var Package[]
     */
    private $parentPackages;

    /**
     * @var int
     */
    private $state;

    /**
     * @var array
     */
    private $params = [];

    /**
     * Deployment state identifier for "in progress" state
     */
    const STATE_PROGRESS = 0;

    /**
     * Deployment state identifier for "completed" state
     */
    const STATE_COMPLETED = 1;

    /**
     * Base area code
     */
    const BASE_AREA = 'base';

    /**
     * Base theme code
     */
    const BASE_THEME = 'Magento/base';

    /**
     * Base locale code
     */
    const BASE_LOCALE = 'default';

    /**
     * @var array
     */
    private $packageDefaultValues = [
        'area' => self::BASE_AREA,
        'theme' => self::BASE_THEME,
        'locale' => self::BASE_LOCALE
    ];

    /**
     * @param PackagePool $packagePool
     * @param FileNameResolver $fileNameResolver
     * @param string $area
     * @param string $theme
     * @param string $locale
     * @param bool $isVirtual
     * @param ProcessorInterface[] $preProcessors
     * @param ProcessorInterface[] $postProcessors
     * @internal param string $type
     */
    public function __construct(
        PackagePool $packagePool,
        FileNameResolver $fileNameResolver,
        $area,
        $theme,
        $locale,
        $isVirtual = false,
        array $preProcessors = [],
        array $postProcessors = []
    ) {
        $this->packagePool = $packagePool;
        $this->fileNameResolver = $fileNameResolver;
        $this->area = $area;
        $this->theme = $theme;
        $this->locale = $locale;
        $this->isVirtual = $isVirtual;
        $this->preProcessors = $preProcessors;
        $this->postProcessors = $postProcessors;
    }

    /**
     * Get area.
     *
     * @return string
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Get parent.
     *
     * @return Package
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get theme.
     *
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Get locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Retrieve package path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getArea() . '/' . $this->getTheme() . '/' . $this->getLocale();
    }

    /**
     * Is package virtual and can not be referenced directly
     *
     * Package considered as "virtual" when not all of the scope identifiers defined (area, theme, locale)
     *
     * @return string
     */
    public function isVirtual()
    {
        return $this->isVirtual;
    }

    /**
     * Get param.
     *
     * @param string $name
     * @return mixed|null
     */
    public function getParam($name)
    {
        return $this->params[$name] ?? null;
    }

    /**
     * Set param.
     *
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
        return true;
    }

    /**
     * Retrieve theme model
     *
     * @return ThemeInterface|null
     */
    public function getThemeModel()
    {
        return $this->packagePool->getThemeModel($this->getArea(), $this->getTheme());
    }

    /**
     * Retrieve file by file id
     *
     * @param string $fileId
     * @return bool|PackageFile
     */
    public function getFile($fileId)
    {
        return $this->files[$fileId] ?? false;
    }

    /**
     * Add file to package
     *
     * @param PackageFile $file
     * @return string
     */
    public function addFile(PackageFile $file)
    {
        if (!$file->getLocale()) {
            $file->setLocale($this->getLocale());
        }
        $this->files[$file->getFileId()] = $file;

        $deployedFilePath = $this->getPath() . '/'
            . ($file->getModule() ? ($file->getModule() . '/') : '')
            . $file->getDeployedFileName();
        $file->setDeployedFilePath($deployedFilePath);

        return $file->getFileId();
    }

    /**
     * Add file to the package map
     *
     * @param PackageFile $file
     * @return void
     */
    public function addFileToMap(PackageFile $file)
    {
        $fileId = $file->getDeployedFileId();
        $this->map[$fileId] = [
            'area' => $this->getArea(),
            'theme' => $this->getTheme(),
            'locale' => $this->getLocale()
        ];
    }

    /**
     * Retrieve all files
     *
     * @return PackageFile[]
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Retrieve files by type
     *
     * @param string $type
     * @return array
     */
    public function getFilesByType($type)
    {
        $files = [];
        /** @var PackageFile $file */
        foreach ($this->getFiles() as $fileId => $file) {
            if (!$file->getFileName()) {
                continue;
            }
            if ($file->getExtension() == $type) {
                $files[$fileId] = $file;
            }
        }
        return $files;
    }

    /**
     * Delete file from package by file id
     *
     * @param string $fileId
     * @return void
     */
    public function deleteFile($fileId)
    {
        $file = $this->files[$fileId];
        $deployedFileId = $file->getDeployedFileId();
        unset($this->files[$fileId]);
        unset($this->map[$deployedFileId]);
    }

    /**
     * Aggregate files from all parent packages
     *
     * Optionally, parent package could be passed
     *
     * @param Package $parentPackage
     * @return bool true on success
     */
    public function aggregate(Package $parentPackage = null)
    {
        $inheritedFiles = $this->getParentFiles();
        foreach ($inheritedFiles as $fileId => $file) {
            /** @var PackageFile $file */
            if (!$this->getFile($fileId)) {
                $file = clone $file;
                $file->setPackage($this);
            }
        }
        if ($parentPackage) {
            $this->setParent($parentPackage);
        }
        return true;
    }

    /**
     * Set parent.
     *
     * @param Package $parent
     * @return bool
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return true;
    }

    /**
     * Retrieve map
     *
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * Get state.
     *
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set state.
     *
     * @param int $state
     * @return bool
     */
    public function setState($state)
    {
        $this->state = $state;
        return true;
    }

    /**
     * Get inheritance level.
     *
     * @return int
     */
    public function getInheritanceLevel()
    {
        $level = 0;
        $theme = $this->getThemeModel();
        if ($theme) {
            ++$level;
            while ($theme = $theme->getParentTheme()) {
                ++$level;
            }
        }
        return $level;
    }

    /**
     * Retrieve inherited package map
     *
     * @return array
     */
    public function getResultMap()
    {
        $map = $this->getMap();
        $parentMap = $this->getParentMap();
        return array_merge($parentMap, $map);
    }

    /**
     * Retrieve parent map
     *
     * @return array
     */
    public function getParentMap()
    {
        $map = [];
        foreach ($this->getParentPackages() as $parentPackage) {
            $map[] = $parentPackage->getMap();
        }
        return array_merge([], ...$map);
    }

    /**
     * Retrieve parent files
     *
     * @param string|null $type
     * @return PackageFile[]
     */
    public function getParentFiles($type = null)
    {
        $files = [];
        foreach ($this->getParentPackages() as $parentPackage) {
            if ($type === null) {
                $files[] = $parentPackage->getFiles();
            } else {
                $files[] = $parentPackage->getFilesByType($type);
            }
        }
        return array_merge([], ...$files);
    }

    /**
     * Retrieve parent packages list
     *
     * @return Package[]
     */
    public function getParentPackages()
    {
        if ($this->parentPackages === null) {
            $this->parentPackages = [];
            $parentPaths = [];
            $this->collectParentPaths(
                $this,
                $this->getArea(),
                $this->getTheme(),
                $this->getLocale(),
                $parentPaths,
                $this->getThemeModel()
            );

            // collect packages in reverse order to have closer ancestor goes later
            foreach (array_reverse($parentPaths) as $path) {
                if ($package = $this->packagePool->getPackage($path)) {
                    $this->parentPackages[$path] = $package;
                }
            }
        }

        return $this->parentPackages;
    }

    /**
     * Get pre processors.
     *
     * @return Processor\ProcessorInterface[]
     */
    public function getPreProcessors()
    {
        return $this->preProcessors;
    }

    /**
     * Get post processors.
     *
     * @return Processor\ProcessorInterface[]
     */
    public function getPostProcessors()
    {
        return $this->postProcessors;
    }

    /**
     * Collect the list of parent packages deployment paths
     *
     * @param Package $package
     * @param string $area
     * @param string $theme
     * @param string $locale
     * @param array $result
     * @param ThemeInterface|null $themeModel
     * @return void
     */
    private function collectParentPaths(
        Package $package,
        $area,
        $theme,
        $locale,
        array &$result = [],
        ThemeInterface $themeModel = null
    ) {
        if (($package->getArea() != $area) || ($package->getTheme() != $theme) || ($package->getLocale() != $locale)) {
            $result[] = $area . '/' . $theme . '/' . $locale;
        }

        if ($locale != $this->packageDefaultValues['locale']) {
            $result[] = $area . '/' . $theme . '/' . $this->packageDefaultValues['locale'];
        }

        if ($themeModel) {
            if ($themeModel->getParentTheme()) {
                $this->collectParentPaths(
                    $package,
                    $area,
                    $themeModel->getParentTheme()->getThemePath(),
                    $package->getLocale(),
                    $result,
                    $themeModel->getParentTheme()
                );
            } else {
                $this->collectParentPaths(
                    $package,
                    $area,
                    $this->packageDefaultValues['theme'],
                    $package->getLocale(),
                    $result
                );
            }
        } else {
            if ($area != $this->packageDefaultValues['area']) {
                $this->collectParentPaths(
                    $package,
                    $this->packageDefaultValues['area'],
                    $theme,
                    $package->getLocale(),
                    $result
                );
            }
        }
    }
}

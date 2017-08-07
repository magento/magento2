<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Package;

use Magento\Deploy\Collector\Collector;
use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Framework\AppInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\Design\Theme\ListInterface;

/**
 * Deployment Packages Pool class
 * @since 2.2.0
 */
class PackagePool
{
    /**
     * @var Collector
     * @since 2.2.0
     */
    private $collector;

    /**
     * @var ThemeInterface[]
     * @since 2.2.0
     */
    private $themes;

    /**
     * @var PackageFactory
     * @since 2.2.0
     */
    private $packageFactory;

    /**
     * @var Package[]
     * @since 2.2.0
     */
    private $packages = [];

    /**
     * @var bool
     * @since 2.2.0
     */
    private $collected = false;

    /**
     * PackagePool constructor
     *
     * @param Collector $collector
     * @param ListInterface $themeCollection
     * @param PackageFactory $packageFactory
     * @since 2.2.0
     */
    public function __construct(
        Collector $collector,
        ListInterface $themeCollection,
        PackageFactory $packageFactory
    ) {
        $this->collector = $collector;
        $themeCollection->clear()->resetConstraints();
        $this->themes = $themeCollection->getItems();
        $this->packageFactory = $packageFactory;
    }

    /**
     * @param string $path
     * @return Package|null
     * @since 2.2.0
     */
    public function getPackage($path)
    {
        $this->collect();
        return isset($this->packages[$path]) ? $this->packages[$path] : null;
    }

    /**
     * @return Package[]
     * @since 2.2.0
     */
    public function getPackages()
    {
        $this->collect();
        return $this->packages;
    }

    /**
     * @param string $areaCode
     * @param string $themePath
     * @return ThemeInterface|null
     * @since 2.2.0
     */
    public function getThemeModel($areaCode, $themePath)
    {
        $theme = $this->getThemeByFullPath($areaCode . '/' . $themePath);
        if ($theme && !$theme->getThemePath()) {
            $theme->setThemePath($themePath);
        }
        return $theme;
    }

    /**
     * @param array $options
     * @return Package[]
     * @since 2.2.0
     */
    public function getPackagesForDeployment(array $options)
    {
        $this->collect();
        $this->ensurePackagesForRequiredLocales($options);

        $toSkip = [];
        $toDeploy = [];
        foreach ($this->packages as $path => $package) {
            if ($this->checkPackageSkip($package, $options)) {
                $toSkip[$path] = $package;
                continue;
            } else {
                $toDeploy[$path] = $package;
            }
        }

        foreach ($toSkip as $path => $package) {
            if (!$this->isAncestorForDeployedPackages($package, $toDeploy)) {
                unset($this->packages[$path]);
            }
        }

        return $this->packages;
    }

    /**
     * Check if package has related child packages which are must be deployed
     *
     * @param Package $excludedPackage
     * @param Package[] $deployedPackages
     * @return bool
     * @since 2.2.0
     */
    private function isAncestorForDeployedPackages(Package $excludedPackage, array $deployedPackages)
    {
        foreach ($deployedPackages as $deployedPackage) {
            $parents = $deployedPackage->getParentPackages();
            if (array_key_exists($excludedPackage->getPath(), $parents)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $fullPath
     * @return ThemeInterface|null
     * @since 2.2.0
     */
    private function getThemeByFullPath($fullPath)
    {
        foreach ($this->themes as $theme) {
            if ($theme->getFullPath() === $fullPath) {
                return $theme;
            }
        }
        return null;
    }

    /**
     * @param bool $recollect
     * @return void
     * @since 2.2.0
     */
    private function collect($recollect = false)
    {
        if (!$this->collected || $recollect) {
            $this->packages = $this->collector->collect();
            $this->collected = true;
        }
    }

    /**
     * Create required packages according to provided options
     *
     * @param array $options
     * @return void
     * @since 2.2.0
     */
    private function ensurePackagesForRequiredLocales(array $options)
    {
        $this->ensureRequiredLocales($options);

        $resultPackages = $this->packages;

        /** @var ThemeInterface $theme */
        foreach ($this->themes as $theme) {
            $inheritedThemes = $theme->getInheritedThemes();
            foreach ($resultPackages as $package) {
                if ($package->getTheme() === Package::BASE_THEME) {
                    continue;
                }
                foreach ($inheritedThemes as $inheritedTheme) {
                    if ($package->getTheme() === $inheritedTheme->getThemePath()
                        && $package->getArea() === $inheritedTheme->getArea()
                    ) {
                        $this->ensurePackage([
                            'area' => $package->getArea(),
                            'theme' => $theme->getThemePath(),
                            'locale' => $package->getLocale(),
                            'isVirtual' => $package->getLocale() == Package::BASE_LOCALE
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Make sure that packages for all requested locales are created
     *
     * @param array $options
     * @return void
     * @since 2.2.0
     */
    private function ensureRequiredLocales(array $options)
    {
        if (empty($options[Options::LANGUAGE]) || $options[Options::LANGUAGE][0] === 'all') {
            $forcedLocales = [AppInterface::DISTRO_LOCALE_CODE];
        } else {
            $forcedLocales = $options[Options::LANGUAGE];
        }

        $resultPackages = $this->packages;
        foreach ($resultPackages as $package) {
            if ($package->getTheme() === Package::BASE_THEME) {
                continue;
            }
            foreach ($forcedLocales as $locale) {
                $this->ensurePackage([
                    'area' => $package->getArea(),
                    'theme' => $package->getTheme(),
                    'locale' => $locale
                ]);
            }
        }
    }

    /**
     * Check if package can be deployed
     *
     * @param Package $package
     * @param array $options
     * @return bool
     * @since 2.2.0
     */
    private function checkPackageSkip(Package $package, array $options)
    {
        return !$this->canDeployArea($package, $options)
        || !$this->canDeployTheme($package, $options)
        || !$this->canDeployLocale($package, $options);
    }

    /**
     * @param Package $package
     * @param array $options
     * @return bool
     * @since 2.2.0
     */
    private function canDeployArea(Package $package, array $options)
    {
        $area = $package->getArea();

        if ($area == 'install') {
            return false;
        }
        if ($area == Package::BASE_AREA) {
            return true;
        }
        $exclude = $this->getOption(Options::EXCLUDE_AREA, $options);
        $include = $this->getOption(Options::AREA, $options);
        return $this->isIncluded($area, $include, $exclude);
    }

    /**
     * @param Package $package
     * @param array $options
     * @return bool
     * @since 2.2.0
     */
    private function canDeployTheme(Package $package, array $options)
    {
        $theme = $package->getTheme();

        if ($theme == Package::BASE_THEME) {
            return true;
        }
        $exclude = $this->getOption(Options::EXCLUDE_THEME, $options);
        $include = $this->getOption(Options::THEME, $options);
        return $this->isIncluded($theme, $include, $exclude);
    }

    /**
     * @param Package $package
     * @param array $options
     * @return bool
     * @since 2.2.0
     */
    private function canDeployLocale(Package $package, array $options)
    {
        $locale = $package->getLocale();
        if ($locale == Package::BASE_LOCALE) {
            return true;
        }
        $exclude = $this->getOption(Options::EXCLUDE_LANGUAGE, $options);
        $include = $this->getOption(Options::LANGUAGE, $options);
        return $this->isIncluded($locale, $include, $exclude);
    }

    /**
     * @param string $entity
     * @param array $includedEntities
     * @param array $excludedEntities
     * @return bool
     * @since 2.2.0
     */
    private function isIncluded($entity, array $includedEntities, array $excludedEntities)
    {
        $result = true;
        if ($includedEntities[0] === 'all' && $excludedEntities[0] === 'none') {
            $result = true;
        } elseif ($excludedEntities[0] !== 'none') {
            $result = !in_array($entity, $excludedEntities);
        } elseif ($includedEntities[0] !== 'all') {
            $result = in_array($entity, $includedEntities);
        }
        return $result;
    }

    /**
     * @param string $name
     * @param array $options
     * @return mixed|null
     * @since 2.2.0
     */
    private function getOption($name, $options)
    {
        return isset($options[$name]) ? $options[$name] : null;
    }

    /**
     * @param array $params
     * @return void
     * @since 2.2.0
     */
    private function ensurePackage(array $params)
    {
        $packagePath = "{$params['area']}/{$params['theme']}/{$params['locale']}";
        if (!isset($this->packages[$packagePath])) {
            $this->packages[$packagePath] = $this->packageFactory->create($params);
        }
    }
}

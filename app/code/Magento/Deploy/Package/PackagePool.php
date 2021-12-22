<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Package;

use Magento\Deploy\Collector\Collector;
use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\Design\Theme\ListInterface;

/**
 * Deployment Packages Pool class
 */
class PackagePool
{
    /**
     * @var Collector
     */
    private $collector;

    /**
     * @var ThemeInterface[]
     */
    private $themes;

    /**
     * @var PackageFactory
     */
    private $packageFactory;

    /**
     * @var Package[]
     */
    private $packages = [];

    /**
     * @var bool
     */
    private $collected = false;

    /**
     * @var LocaleResolver|null
     */
    private $localeResolver;

    /**
     * PackagePool constructor
     *
     * @param Collector $collector
     * @param ListInterface $themeCollection
     * @param PackageFactory $packageFactory
     * @param LocaleResolver|null $localeResolver
     */
    public function __construct(
        Collector $collector,
        ListInterface $themeCollection,
        PackageFactory $packageFactory,
        ?LocaleResolver $localeResolver = null
    ) {
        $this->collector = $collector;
        $themeCollection->clear()->resetConstraints();
        $this->themes = $themeCollection->getItems();
        $this->packageFactory = $packageFactory;
        $this->localeResolver = $localeResolver ?: ObjectManager::getInstance()->get(LocaleResolver::class);
    }

    /**
     * Return package
     *
     * @param string $path
     * @return Package|null
     */
    public function getPackage($path)
    {
        $this->collect();
        return $this->packages[$path] ?? null;
    }

    /**
     * Return packages
     *
     * @return Package[]
     */
    public function getPackages()
    {
        $this->collect();
        return $this->packages;
    }

    /**
     * Return theme model
     *
     * @param string $areaCode
     * @param string $themePath
     * @return ThemeInterface|null
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
     * Return packages from deployment
     *
     * @param array $options
     * @return Package[]
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
     * Return theme by full path
     *
     * @param string $fullPath
     * @return ThemeInterface|null
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
     * Collect packages
     *
     * @param bool $recollect
     * @return void
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
     */
    private function ensureRequiredLocales(array $options)
    {
        if (empty($options[Options::LANGUAGE]) || $options[Options::LANGUAGE][0] === 'all') {
            $forcedLocales = [];
        } else {
            $forcedLocales = $options[Options::LANGUAGE];
        }

        foreach ($this->packages as $package) {
            if ($package->getTheme() === Package::BASE_THEME) {
                continue;
            }

            $locales = $forcedLocales ?: $this->localeResolver->getUsedPackageLocales($package);
            foreach ($locales as $locale) {
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
     */
    private function checkPackageSkip(Package $package, array $options)
    {
        return !$this->canDeployArea($package, $options)
        || !$this->canDeployTheme($package, $options)
        || !$this->canDeployLocale($package, $options);
    }

    /**
     * Check if can deploy area
     *
     * @param Package $package
     * @param array $options
     * @return bool
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
     * Verify can deploy theme
     *
     * @param Package $package
     * @param array $options
     * @return bool
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
     * Verify can deploy locale
     *
     * @param Package $package
     * @param array $options
     * @return bool
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
     * Check if included entity
     *
     * @param string $entity
     * @param array $includedEntities
     * @param array $excludedEntities
     * @return bool
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
     * Return option by name
     *
     * @param string $name
     * @param array $options
     * @return mixed|null
     */
    private function getOption($name, $options)
    {
        return $options[$name] ?? null;
    }

    /**
     * Ensure package exist
     *
     * @param array $params
     * @return void
     */
    private function ensurePackage(array $params)
    {
        $packagePath = "{$params['area']}/{$params['theme']}/{$params['locale']}";
        if (!isset($this->packages[$packagePath])) {
            $this->packages[$packagePath] = $this->packageFactory->create($params);
        }
    }
}

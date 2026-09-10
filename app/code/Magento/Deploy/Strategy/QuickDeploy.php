<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Deploy\Strategy;

use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackagePool;
use Magento\Deploy\Process\Queue;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Translate\Js\Config as JsTranslationConfig;
use function array_key_exists;

/**
 * Quick deployment strategy implementation
 */
class QuickDeploy implements StrategyInterface
{
    /**
     * @var PackagePool
     */
    private $packagePool;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var array
     */
    private $baseLocalePackages = [];

    /**
     * @var Package[]
     */
    private $baseLocaleParents = [];

    /**
     * @var JsTranslationConfig
     */
    private $jsTranslationConfig;

    /**
     * QuickDeploy constructor
     *
     * @param PackagePool $packagePool
     * @param Queue $queue
     * @param JsTranslationConfig|null $jsTranslationConfig
     */
    public function __construct(
        PackagePool $packagePool,
        Queue $queue,
        ?JsTranslationConfig $jsTranslationConfig = null
    ) {
        $this->packagePool = $packagePool;
        $this->queue = $queue;
        $this->jsTranslationConfig = $jsTranslationConfig
            ?: ObjectManager::getInstance()->get(JsTranslationConfig::class);
    }

    /**
     * @inheritdoc
     */
    public function deploy(array $options)
    {
        $groupedPackages = $deployPackages = [];
        $packages = $this->packagePool->getPackagesForDeployment($options);
        foreach ($packages as $package) {
            if ($package->isVirtual()) {
                // skip packages which can not be referenced directly
                continue;
            }
            $level = $this->getInheritanceLevel($package);
            $groupedPackages[$level][$package->getPath()] = $package;
        }

        ksort($groupedPackages);

        foreach ($groupedPackages as $level => $levelPackages) {
            $this->preparePackages($level, $levelPackages);
        }

        $parentCompilationRequested = $options[Options::NO_PARENT] !== true;
        $includeThemesMap = array_flip($options[Options::THEME] ?? []);
        $excludeThemesMap = array_flip($options[Options::EXCLUDE_THEME] ?? []);

        foreach ($groupedPackages as $levelPackages) {
            foreach ($levelPackages as $package) {
                if ($parentCompilationRequested
                    || $this->canDeployTheme($package->getTheme(), $includeThemesMap, $excludeThemesMap)) {
                    $this->queue->add($package, $this->getDeploymentDependencies($package));
                    $deployPackages[] = $package;
                }
            }
        }

        $this->queue->process();

        return $deployPackages;
    }

    /**
     * Prepare packages before deploying
     *
     * @param int $level
     * @param Package[] $levelPackages
     * @return void
     */
    private function preparePackages(int $level, array $levelPackages): void
    {
        foreach ($levelPackages as $package) {
            $package->aggregate();
            if ($level > 1) {
                $parentPackage = $this->resolveParentPackage($package);
                if ($parentPackage) {
                    $package->setParent($parentPackage);
                }
            }
        }
    }

    /**
     * Retrieve package which deployed files can be reused for the given package
     *
     * @param Package $package
     * @return Package|null
     */
    private function resolveParentPackage(Package $package): ?Package
    {
        $packageId = $package->getArea() . '/' . $package->getTheme();
        $baseLocalePackage = $this->baseLocalePackages[$packageId] ?? null;
        // use base package if it is not the same as current
        if ($baseLocalePackage
            && $package !== $baseLocalePackage
            && $this->canReuseBaseLocalePackage($package, $baseLocalePackage)
        ) {
            $this->baseLocaleParents[$package->getPath()] = $baseLocalePackage;
            return $baseLocalePackage;
        }

        $parentPackage = null;
        foreach (array_reverse($package->getParentPackages()) as $ancestorPackage) {
            if (!$ancestorPackage->isVirtual()) {
                return $ancestorPackage;
            }
            if ($parentPackage === null) {
                $parentPackage = $ancestorPackage;
            }
        }

        return $parentPackage;
    }

    /**
     * Check if deployed files of the base locale package can be reused for the given package
     *
     * @param Package $package
     * @param Package $baseLocalePackage
     * @return bool
     */
    private function canReuseBaseLocalePackage(Package $package, Package $baseLocalePackage): bool
    {
        // only the dictionary strategy keeps deployed JS files free of per-locale embedded translations
        // @see \Magento\Translation\Model\Js\PreProcessor::process
        if (!$this->jsTranslationConfig->dictionaryEnabled()) {
            return false;
        }

        return !$this->hasLocaleSpecificFiles($package) && !$this->hasLocaleSpecificFiles($baseLocalePackage);
    }

    /**
     * Check if package has own files, which are collected from "web/i18n/<locale>" directories
     *
     * @param Package $package
     * @return bool
     */
    private function hasLocaleSpecificFiles(Package $package): bool
    {
        foreach ($package->getFiles() as $file) {
            if ($file->getOrigPackage() === $package) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve packages which must be deployed before the given one
     *
     * @param Package $package
     * @return Package[]
     */
    private function getDeploymentDependencies(Package $package): array
    {
        $baseLocalePackage = $this->baseLocaleParents[$package->getPath()] ?? null;

        return $baseLocalePackage ? [$baseLocalePackage->getPath() => $baseLocalePackage] : [];
    }

    /**
     * Calculate proper inheritance level for the given package
     *
     * @param Package $package
     * @return int
     */
    private function getInheritanceLevel(Package $package): int
    {
        $level = $package->getInheritanceLevel();
        $packageId = $package->getArea() . '/' . $package->getTheme();
        if (!isset($this->baseLocalePackages[$packageId])) {
            $this->baseLocalePackages[$packageId] = $package;
        } else {
            ++$level;
        }
        return $level;
    }

    /**
     * Verify if specified theme should be deployed
     *
     * @param string $theme
     * @param array $includedThemesMap
     * @param array $excludedEntitiesMap
     * @return bool
     */
    private function canDeployTheme(string $theme, array $includedThemesMap, array $excludedEntitiesMap): bool
    {
        $includesAllThemes = array_key_exists('all', $includedThemesMap);
        $excludesNoneThemes = array_key_exists('none', $excludedEntitiesMap);

        if ($includesAllThemes && $excludesNoneThemes) {
            return true;
        } elseif (!$excludesNoneThemes) {
            return !array_key_exists($theme, $excludedEntitiesMap);
        } elseif (!$includesAllThemes) {
            return array_key_exists($theme, $includedThemesMap);
        }

        return true;
    }
}

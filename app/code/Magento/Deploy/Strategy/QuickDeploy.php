<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Strategy;

use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackagePool;
use Magento\Deploy\Process\Queue;
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
     * QuickDeploy constructor
     *
     * @param PackagePool $packagePool
     * @param Queue $queue
     */
    public function __construct(
        PackagePool $packagePool,
        Queue $queue
    ) {
        $this->packagePool = $packagePool;
        $this->queue = $queue;
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
                    $this->queue->add($package);
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
                $parentPackage = null;
                $packageId = $package->getArea() . '/' . $package->getTheme();
                // use base package if it is not the same as current
                if (isset($this->baseLocalePackages[$packageId])
                    && $package !== $this->baseLocalePackages[$packageId]
                ) {
                    $parentPackage = $this->baseLocalePackages[$packageId];
                } else {
                    $parentPackages = $package->getParentPackages();
                    foreach (array_reverse($parentPackages) as $ancestorPackage) {
                        if (!$ancestorPackage->isVirtual()) {
                            $parentPackage = $ancestorPackage;
                            break;
                        }
                        if ($parentPackage === null) {
                            $parentPackage = $ancestorPackage;
                        }
                    }
                }
                if ($parentPackage) {
                    $package->setParent($parentPackage);
                }
            }
        }
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

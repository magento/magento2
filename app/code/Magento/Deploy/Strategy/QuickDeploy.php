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
        $groupedPackages = [];
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

        $deployPackages = $this->preparePackages($groupedPackages, $options);
        foreach ($groupedPackages as $level => $levelPackages) {
            foreach (array_intersect_key($levelPackages, $deployPackages) as $package) {
                $this->addToQueue($package, $level, $deployPackages);
            }
        }

        $this->queue->process();

        return array_values($deployPackages);
    }

    /**
     * Aggregate files of all packages and return the ones requested for deployment
     *
     * @param array $groupedPackages
     * @param array $options
     * @return Package[]
     */
    private function preparePackages(array $groupedPackages, array $options): array
    {
        $parentCompilationRequested = $options[Options::NO_PARENT] !== true;
        $includeThemesMap = array_flip($options[Options::THEME] ?? []);
        $excludeThemesMap = array_flip($options[Options::EXCLUDE_THEME] ?? []);

        $deployPackages = [];
        foreach ($groupedPackages as $levelPackages) {
            foreach ($levelPackages as $path => $package) {
                $package->aggregate();
                if ($parentCompilationRequested
                    || $this->canDeployTheme($package->getTheme(), $includeThemesMap, $excludeThemesMap)) {
                    $deployPackages[$path] = $package;
                }
            }
        }
        return $deployPackages;
    }

    /**
     * Add package to the queue, depending on its parent package when the parent is deployed in the same run
     *
     * A package may copy files from its parent, so the parent must be complete and must not be stale output
     * of an earlier deployment.
     *
     * @param Package $package
     * @param int $level
     * @param Package[] $deployPackages
     * @return void
     */
    private function addToQueue(Package $package, int $level, array $deployPackages): void
    {
        $candidate = $level > 1 ? $this->findParentPackage($package) : null;
        $parentPackage = $candidate ? ($deployPackages[$candidate->getPath()] ?? null) : null;
        if ($parentPackage) {
            $package->setParent($parentPackage);
            // flag is required to copy unchanged files from the parent package
            /* @see \Magento\Deploy\Service\DeployPackage::checkIfCanCopy() */
            $package->setParam('copy_from_parent', true);
        }
        $this->queue->add($package, $parentPackage ? [$parentPackage->getPath() => $parentPackage] : []);
    }

    /**
     * Find the base locale package of the same theme, or the closest non-virtual ancestor package
     *
     * @param Package $package
     * @return Package|null
     */
    private function findParentPackage(Package $package): ?Package
    {
        $packageId = $package->getArea() . '/' . $package->getTheme();
        // use base package if it is not the same as current
        if (isset($this->baseLocalePackages[$packageId]) && $package !== $this->baseLocalePackages[$packageId]) {
            return $this->baseLocalePackages[$packageId];
        }
        foreach (array_reverse($package->getParentPackages()) as $ancestorPackage) {
            if (!$ancestorPackage->isVirtual()) {
                return $ancestorPackage;
            }
        }
        return null;
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

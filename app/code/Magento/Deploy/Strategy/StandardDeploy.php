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

/**
 * Standard deployment strategy implementation
 */
class StandardDeploy implements StrategyInterface
{
    /**
     * Package pool object
     *
     * @var PackagePool
     */
    private $packagePool;

    /**
     * Deployment queue
     *
     * @var Queue
     */
    private $queue;

    /**
     * StandardDeploy constructor
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
        $deployedPackages = [];
        $packages = $this->packagePool->getPackagesForDeployment($options);
        foreach ($packages as $package) {
            /** @var Package $package */
            if ($package->isVirtual()) {
                // skip packages which can not be referenced directly from web ...
                continue;
            }
            // ... and aggregate files from ancestors for others
            $package->aggregate();
            $deployedPackages[] = $package;
        }

        // For each area/theme, the first locale becomes the base. Subsequent locales are set as
        // children of the base so DeployPackage can bulk-copy the already-deployed output rather
        // than re-running LESS compilation for every locale variant independently.
        $baseLocalePackages = [];
        foreach ($deployedPackages as $package) {
            $packageId = $package->getArea() . '/' . $package->getTheme();
            if (!isset($baseLocalePackages[$packageId])) {
                $baseLocalePackages[$packageId] = $package;
            } else {
                $package->setParent($baseLocalePackages[$packageId]);
            }
        }

        $parentCompilationRequested = $options[Options::NO_PARENT] !== true;
        $includeThemesMap = array_flip($options[Options::THEME] ?? []);
        $excludeThemesMap = array_flip($options[Options::EXCLUDE_THEME] ?? []);

        // Sort so base packages (no locale parent) come before their variants.
        // This guarantees the source directory exists when copyTree runs for each variant.
        usort($deployedPackages, fn($a, $b) => ($a->getParent() !== null) <=> ($b->getParent() !== null));

        foreach ($deployedPackages as $package) {
            if ($parentCompilationRequested
                || $this->canDeployTheme($package->getTheme(), $includeThemesMap, $excludeThemesMap)) {
                $parentPackage = $package->getParent();
                $this->queue->add(
                    $package,
                    $parentPackage ? [$parentPackage->getPath() => $parentPackage] : []
                );
            }
        }

        $this->queue->process();

        return $deployedPackages;
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

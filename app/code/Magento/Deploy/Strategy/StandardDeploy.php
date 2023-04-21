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

        $parentCompilationRequested = $options[Options::NO_PARENT] !== true;
        $includeThemesMap = array_flip($options[Options::THEME] ?? []);
        $excludeThemesMap = array_flip($options[Options::EXCLUDE_THEME] ?? []);

        foreach ($deployedPackages as $package) {
            if ($parentCompilationRequested
                || $this->canDeployTheme($package->getTheme(), $includeThemesMap, $excludeThemesMap)) {
                $this->queue->add($package);
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

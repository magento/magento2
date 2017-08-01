<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Strategy;

use Magento\Deploy\Package\PackagePool;
use Magento\Deploy\Package\Package;
use Magento\Deploy\Process\Queue;

/**
 * Quick deployment strategy implementation
 * @since 2.2.0
 */
class QuickDeploy implements StrategyInterface
{
    /**
     * @var PackagePool
     * @since 2.2.0
     */
    private $packagePool;

    /**
     * @var Queue
     * @since 2.2.0
     */
    private $queue;

    /**
     * @var array
     * @since 2.2.0
     */
    private $baseLocalePackages = [];

    /**
     * QuickDeploy constructor
     *
     * @param PackagePool $packagePool
     * @param Queue $queue
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function deploy(array $options)
    {
        $groupedPackages = $deployPackages = [];
        $packages = $this->packagePool->getPackagesForDeployment($options);
        foreach ($packages as $package) {
            /** @var Package $package */
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

        foreach ($groupedPackages as $levelPackages) {
            foreach ($levelPackages as $package) {
                $this->queue->add($package);
                $deployPackages[] = $package;
            }
        }

        $this->queue->process();

        return $deployPackages;
    }

    /**
     * @param int $level
     * @param Package[] $levelPackages
     * @return void
     * @since 2.2.0
     */
    private function preparePackages($level, array $levelPackages)
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
     * @since 2.2.0
     */
    private function getInheritanceLevel(Package $package)
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
}

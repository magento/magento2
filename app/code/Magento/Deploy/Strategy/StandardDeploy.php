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

        foreach ($deployedPackages as $package) {
            $this->queue->add($package);
        }

        $this->queue->process();

        return $deployedPackages;
    }
}

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
 * @since 2.2.0
 */
class StandardDeploy implements StrategyInterface
{
    /**
     * Package pool object
     *
     * @var PackagePool
     * @since 2.2.0
     */
    private $packagePool;

    /**
     * Deployment queue
     *
     * @var Queue
     * @since 2.2.0
     */
    private $queue;

    /**
     * StandardDeploy constructor
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

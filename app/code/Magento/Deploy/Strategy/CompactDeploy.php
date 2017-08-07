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
 * Compact deployment strategy implementation
 * @since 2.2.0
 */
class CompactDeploy implements StrategyInterface
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
     * CompactDeploy constructor
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
        $packages = $this->packagePool->getPackagesForDeployment($options);
        foreach ($packages as $package) {
            /* @var Package $package */
            // set closest ancestor package as parent
            $parentPackages = $package->getParentPackages();
            $package->setParent(array_pop($parentPackages));

            if (!$package->isVirtual()) {
                // flag is required to enable "Package Map files" post-processor
                /* @see \Magento\Deploy\Package\Processor\PostProcessor\Map */
                $package->setParam('build_map', true);
            }

            // set all parent packages as dependencies for current package deployment task
            $this->queue->add($package, $package->getParentPackages());
        }

        $this->queue->process();

        return $packages;
    }
}

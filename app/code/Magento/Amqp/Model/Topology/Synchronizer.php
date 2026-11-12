<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Amqp\Model\Topology;

use Magento\Framework\Amqp\TopologyInstaller;
use Magento\Framework\MessageQueue\Topology\SynchronizerInterface;

class Synchronizer implements SynchronizerInterface
{
    /**
     * @param TopologyInstaller $topologyInstaller
     */
    public function __construct(private readonly TopologyInstaller $topologyInstaller)
    {
    }

    /**
     * @inheritdoc
     */
    public function synchronize(): array
    {
        return $this->topologyInstaller->declareTopology();
    }
}

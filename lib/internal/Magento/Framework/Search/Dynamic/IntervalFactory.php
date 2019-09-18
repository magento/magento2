<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Dynamic;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\EngineResolverInterface;

/**
 * @api
 * @since 100.0.2
 */
class IntervalFactory
{
    /**
     * @var string
     */
    private $interval;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param EngineResolverInterface $engineResolver
     * @param string[] $intervals
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        EngineResolverInterface $engineResolver,
        $intervals
    ) {
        $this->objectManager = $objectManager;
        $configValue = $engineResolver->getCurrentSearchEngine();
        if (isset($intervals[$configValue])) {
            $this->interval = $intervals[$configValue];
        } else {
            throw new \LogicException("Interval not found by config {$configValue}");
        }
    }

    /**
     * Create interval
     *
     * @param array $data
     * @return IntervalInterface
     */
    public function create(array $data = [])
    {
        $interval = $this->objectManager->create($this->interval, $data);
        if (!$interval instanceof IntervalInterface) {
            throw new \LogicException(
                'Interval not instance of interface ' . IntervalInterface::class
            );
        }
        return $interval;
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Dynamic;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\ObjectManagerInterface;

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
     * @param ScopeConfigInterface $scopeConfig
     * @param string $configPath
     * @param string[] $intervals
     * @param string $scope
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        $configPath,
        $intervals,
        $scope = ScopeInterface::SCOPE_DEFAULT
    ) {
        $this->objectManager = $objectManager;
        $configValue = $scopeConfig->getValue($configPath, $scope);
        if (isset($intervals[$configValue])) {
            $this->interval = $intervals[$configValue];
        } else {
            throw new \LogicException("Interval not found by config {$configValue}");
        }
    }

    /**
     * Create interval
     *
     * @return IntervalInterface
     */
    public function create()
    {
        $interval = $this->objectManager->create($this->interval);
        if (!$interval instanceof IntervalInterface) {
            throw new \LogicException(
                'Interval not instance of interface \Magento\Framework\Search\Dynamic\IntervalInterface'
            );
        }
        return $interval;
    }
}

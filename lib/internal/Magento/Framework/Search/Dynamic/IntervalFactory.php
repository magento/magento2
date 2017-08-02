<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Dynamic;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * @api
 * @since 2.0.0
 */
class IntervalFactory
{
    /**
     * @var string
     * @since 2.0.0
     */
    private $interval;

    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     * @param string $configPath
     * @param string[] $intervals
     * @param string $scope
     * @since 2.0.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        $configPath,
        $intervals,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
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
     * @param array $data
     * @return IntervalInterface
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        $interval = $this->objectManager->create($this->interval, $data);
        if (!$interval instanceof IntervalInterface) {
            throw new \LogicException(
                'Interval not instance of interface \Magento\Framework\Search\Dynamic\IntervalInterface'
            );
        }
        return $interval;
    }
}

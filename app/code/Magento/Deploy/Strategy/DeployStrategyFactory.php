<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Strategy;

use Magento\Framework\Exception\InputException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Abstract factory class for instances of @see \Magento\Deploy\Strategy\StrategyInterface
 * @since 2.2.0
 */
class DeployStrategyFactory
{
    /**
     * Standard deploy strategy
     */
    const DEPLOY_STRATEGY_STANDARD = 'standard';

    /**
     * Quick deploy strategy
     */
    const DEPLOY_STRATEGY_QUICK = 'quick';

    /**
     * Standard deploy strategy
     */
    const DEPLOY_STRATEGY_COMPACT = 'compact';

    /**
     * @var ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * Deployment strategies
     *
     * @var array
     * @since 2.2.0
     */
    private $strategies = [];

    /**
     * DeployStrategyFactory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $strategies
     * @since 2.2.0
     */
    public function __construct(ObjectManagerInterface $objectManager, array $strategies = [])
    {
        $this->objectManager = $objectManager;
        $defaultStrategies = [
            self::DEPLOY_STRATEGY_STANDARD => StandardDeploy::class,
            self::DEPLOY_STRATEGY_QUICK => QuickDeploy::class,
            self::DEPLOY_STRATEGY_COMPACT => CompactDeploy::class,
        ];
        $this->strategies = array_replace($defaultStrategies, $strategies);
    }

    /**
     * Create new instance of deployment strategy
     *
     * @param string $type
     * @param array $arguments
     * @return StrategyInterface
     * @throws InputException
     * @since 2.2.0
     */
    public function create($type, array $arguments = [])
    {
        $type = $type ?: self::DEPLOY_STRATEGY_STANDARD;
        if (!isset($this->strategies[$type])) {
            throw new InputException(__('Wrong deploy strategy type: %1', $type));
        }
        return $this->objectManager->create($this->strategies[$type], $arguments);
    }
}

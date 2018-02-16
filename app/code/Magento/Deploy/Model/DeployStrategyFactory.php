<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model;

use Magento\Deploy\Model\Deploy\DeployInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\ObjectManagerInterface;

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
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $type
     * @param array $arguments
     * @return DeployInterface
     * @throws InputException
     */
    public function create($type, array $arguments = [])
    {
        $strategyMap = [
            self::DEPLOY_STRATEGY_STANDARD => Deploy\LocaleDeploy::class,
            self::DEPLOY_STRATEGY_QUICK => Deploy\LocaleQuickDeploy::class,
        ];

        if (!isset($strategyMap[$type])) {
            throw new InputException(__('Wrong deploy strategy type: %1', $type));
        }

        return $this->objectManager->create($strategyMap[$type], $arguments);
    }
}

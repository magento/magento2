<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model;

use Magento\Deploy\Model\Deploy\DeployInterface;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\State;

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
     * @var ObjectManagerFactory
     */
    private $objectManagerFactory;

    /**
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(ObjectManagerFactory $objectManagerFactory)
    {
        $this->objectManagerFactory = $objectManagerFactory;
    }

    /**
     * @param string $areaCode
     * @param string $type
     * @param array $arguments
     * @return DeployInterface
     */
    public function create($areaCode, $type, array $arguments = [])
    {
        $strategyMap = [
            self::DEPLOY_STRATEGY_STANDARD => Deploy\LocaleDeploy::class,
            self::DEPLOY_STRATEGY_QUICK => Deploy\LocaleQuickDeploy::class,
        ];

        if (!isset($strategyMap[$type])) {
            throw new \InvalidArgumentException('Wrong deploy strategy type: ' . $type);
        }
        $objectManager = $this->objectManagerFactory->create([State::PARAM_MODE => State::MODE_PRODUCTION]);
        $objectManager->get(State::class)->setAreaCode($areaCode);

        return $objectManager->create($strategyMap[$type], $arguments);
    }
}

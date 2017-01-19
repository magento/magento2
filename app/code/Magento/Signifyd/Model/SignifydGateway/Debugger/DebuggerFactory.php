<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Debugger;

use Magento\Framework\ObjectManagerInterface;
use Magento\Signifyd\Model\Config;

/**
 * Factory produces debugger based on runtime configuration.
 *
 * Configuration may be changed by
 *  - config.xml
 *  - at Admin panel (Stores > Configuration > Sales > Fraud Detection > Signifyd > Debug)
 */
class DebuggerFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * DebuggerFactory constructor.
     *
     * @param bjectManagerInterface $objectManager
     * @param Config $config
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Config $config
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
    }

    /**
     * Create debugger instance
     *
     * @return DebuggerInterface
     */
    public function create()
    {
        if (!$this->config->isDebugModeEnabled()) {
            return $this->objectManager->get(BlackHole::class);
        }

        return $this->objectManager->get(Log::class);
    }
}

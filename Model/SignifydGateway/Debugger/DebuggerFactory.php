<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * @since 2.2.0
 */
class DebuggerFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * @var Config
     * @since 2.2.0
     */
    private $config;

    /**
     * DebuggerFactory constructor.
     *
     * @param bjectManagerInterface $objectManager
     * @param Config $config
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function create()
    {
        if (!$this->config->isDebugModeEnabled()) {
            return $this->objectManager->get(BlackHole::class);
        }

        return $this->objectManager->get(Log::class);
    }
}

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
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
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
     * @param ObjectManagerInterface $objectManager
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
     * @param int|null $storeId
     * @return DebuggerInterface
     */
    public function create($storeId = null): DebuggerInterface
    {
        if (!$this->config->isDebugModeEnabled($storeId)) {
            return $this->objectManager->get(BlackHole::class);
        }

        return $this->objectManager->get(Log::class);
    }
}

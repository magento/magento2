<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Framework\App\State;

use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\ObjectManagerInterface;

/**
 * Framework specific reset state
 */
class ReloadProcessor implements ReloadProcessorInterface
{

    public function __construct(
        private DeploymentConfig $deploymentConfig,
        private ScopeCodeResolver $scopeCodeResolver
    )
    {
    }

    /**
     * Tells the system state to reload itself.
     *
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function reloadState()
    {
        $this->deploymentConfig->resetData();
        $this->scopeCodeResolver->clean();
    }
}

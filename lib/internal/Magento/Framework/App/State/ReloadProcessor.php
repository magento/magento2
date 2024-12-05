<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Framework\App\State;

use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\DeploymentConfig;

/**
 * Framework specific reset state
 */
class ReloadProcessor implements ReloadProcessorInterface
{

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param ScopeCodeResolver $scopeCodeResolver
     */
    public function __construct(
        private readonly DeploymentConfig $deploymentConfig,
        private readonly ScopeCodeResolver $scopeCodeResolver
    ) {
    }

    /**
     * Tells the system state to reload itself.
     *
     * @return void
     */
    public function reloadState(): void
    {
        $this->deploymentConfig->resetData();
        $this->scopeCodeResolver->clean();
    }
}

<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\TestFramework\Workaround;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Deployment config handler.
 *
 * @package Magento\TestFramework\Workaround
 */
class DeploymentConfig
{
    /**
     * Start test.
     *
     * @return void
     */
    public function startTest()
    {
        /** @var \Magento\Framework\App\DeploymentConfig $deploymentConfig */
        $deploymentConfig = Bootstrap::getObjectManager()->get(\Magento\Framework\App\DeploymentConfig::class);
        $deploymentConfig->resetData();
    }
}

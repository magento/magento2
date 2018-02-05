<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\Cli;

use Magento\Mtf\Util\Command\Cli;

/**
 * Setup Magento for tests executions.
 */
class DeployMode extends Cli
{
    /**
     * Parameter for Magento command to set the deploy mode
     */
    const PARAM_DEPLOY_MODE_DEVELOPER = 'deploy:mode:set developer';

    /**
     * Parameter for Magento command to set the deploy mode to Production
     */
    const PARAM_DEPLOY_MODE_PRODUCTION = 'deploy:mode:set production';

    /**
     * set the deployment mode to developer
     *
     * @return void
     */
    public function setDeployModeToDeveloper()
    {
        parent::execute(DeployMode::PARAM_DEPLOY_MODE_DEVELOPER);
    }

    /**
     * set the deployment mode to production
     *
     * @return void
     */
    public function setDeployModeToProduction()
    {
        parent::execute(DeployMode::PARAM_DEPLOY_MODE_PRODUCTION);
    }
}

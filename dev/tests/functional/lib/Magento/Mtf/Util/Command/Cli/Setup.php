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
class Setup extends Cli
{
    /**
     * Parameter for uninstall Magento command.
     */
    const PARAM_SETUP_UNINSTALL = 'setup:uninstall';

    /**
     * Parameter for DI compile Magento command.
     */
    const PARAM_SETUP_DI_COMPILE = 'setup:di:compile';

    /**
     * Uninstall Magento.
     *
     * @return void
     */
    public function uninstall()
    {
        parent::execute(Setup::PARAM_SETUP_UNINSTALL);
    }

    /**
     * DI Compile.
     *
     * @return void
     */
    public function diCompile()
    {
        parent::execute(Setup::PARAM_SETUP_DI_COMPILE);
    }
}

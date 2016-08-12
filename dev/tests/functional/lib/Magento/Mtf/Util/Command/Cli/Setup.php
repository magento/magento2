<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\Cli;

use Magento\Mtf\Util\Command\Cli;

/**
 * Setup magento for tests executions.
 */
class Setup extends Cli
{
    /**
     * Parameter for uninstall magento command.
     */
    const PARAM_SETUP_UNINSTALL = 'setup:uninstall';

    /**
     * Uninstall magento.
     *
     * @return void
     */
    public function uninstall()
    {
        parent::execute(Setup::PARAM_SETUP_UNINSTALL);
    }
}

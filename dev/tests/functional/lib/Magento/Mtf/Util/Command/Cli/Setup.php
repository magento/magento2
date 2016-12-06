<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * Options for uninstall Magento command.
     *
     * @var array
     */
    private $options = ['-n'];

    /**
     * Uninstall Magento.
     *
     * @return void
     */
    public function uninstall()
    {
        parent::execute(Setup::PARAM_SETUP_UNINSTALL, $this->options);
    }
}

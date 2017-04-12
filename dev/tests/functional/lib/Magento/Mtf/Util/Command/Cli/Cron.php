<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\Cli;

use Magento\Mtf\Util\Command\Cli;

/**
 * Handle cron for tests executions.
 */
class Cron extends Cli
{
    /**
     * Parameter for cron run command.
     */
    const PARAM_CRON_RUN = 'cron:run';

    /**
     * Run cron.
     *
     * @return void
     */
    public function run()
    {
        parent::execute(Cron::PARAM_CRON_RUN);
        sleep(60); // According to Magento 2 documentation cron job running takes 600 ms
    }
}

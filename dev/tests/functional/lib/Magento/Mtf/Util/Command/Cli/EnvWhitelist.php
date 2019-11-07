<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\Cli;

use Magento\Mtf\Util\Command\Cli;

/**
 * Adding and removing domain to whitelist for test execution.
 */
class EnvWhitelist extends Cli
{
    /**
     * Parameter domain add command.
     */
    const PARAM_DOMAINS = 'downloadable:domains';

    /**
     * Add host to the whitelist.
     *
     * @param string $host
     */
    public function addHost($host)
    {
        parent::execute(EnvWhitelist::PARAM_DOMAINS . ':add ' . $host);
    }

    /**
     * Remove host from the whitelist.
     *
     * @param string $host
     */
    public function removeHost($host)
    {
        parent::execute(EnvWhitelist::PARAM_DOMAINS . ':remove ' . $host);
    }
}

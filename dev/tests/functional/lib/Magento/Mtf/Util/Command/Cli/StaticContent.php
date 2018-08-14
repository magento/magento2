<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\Cli;

use Magento\Mtf\Util\Command\Cli;

/**
 * Merchant Developer deploys static view files during test executions so that Storefront UI updates are applied.
 */
class StaticContent extends Cli
{
    /**
     * Parameter for deploy static view files.
     */
    const PARAM_SETUP_STATIC_CONTENT_DEPLOY = 'setup:static-content:deploy';

    /**
     * Deploy static view files.
     *
     * @return void
     */
    public function deploy()
    {
        parent::execute(StaticContent::PARAM_SETUP_STATIC_CONTENT_DEPLOY);
    }
}

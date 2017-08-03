<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Console\Command;

/**
 * Command for disabling cache
 *
 * @api
 * @since 2.0.0
 */
class CacheDisableCommand extends AbstractCacheSetCommand
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function configure()
    {
        $this->setName('cache:disable');
        $this->setDescription('Disables cache type(s)');
        parent::configure();
    }

    /**
     * Is Disable cache
     *
     * @return bool
     * @since 2.0.0
     */
    protected function isEnable()
    {
        return false;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Console\Command;

/**
 * Command for disabling cache
 */
class CacheDisableCommand extends AbstractCacheSetCommand
{
    /**
     * {@inheritdoc}
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
     */
    protected function isEnable()
    {
        return false;
    }
}

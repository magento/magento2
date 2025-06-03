<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Backend\Console\Command;

/**
 * Command for disabling cache
 *
 * @api
 * @since 100.0.2
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

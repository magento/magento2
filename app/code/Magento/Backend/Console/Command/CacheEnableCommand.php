<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Backend\Console\Command;

/**
 * Command for enabling cache
 *
 * @api
 * @since 100.0.2
 */
class CacheEnableCommand extends AbstractCacheSetCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cache:enable');
        $this->setDescription('Enables cache type(s)');
        parent::configure();
    }

    /**
     * Is enable cache
     *
     * @return bool
     */
    protected function isEnable()
    {
        return true;
    }
}

<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Console\Command;

/**
 * Command for enabling cache
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

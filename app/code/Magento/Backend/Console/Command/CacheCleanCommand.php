<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Console\Command;

/**
 * Command for cleaning cache
 */
class CacheCleanCommand extends AbstractCacheTypeManageCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cache:clean');
        $this->setDescription('Cleans cache type(s)');
        parent::configure();
    }

    /**
     * Cleans cache types
     *
     * @param array $cacheTypes
     * @return void
     */
    protected function performAction(array $cacheTypes)
    {
        $this->cacheManager->clean($cacheTypes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDisplayMessage()
    {
        return 'Cleaned cache types:';
    }
}

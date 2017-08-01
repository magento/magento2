<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Console\Command;

/**
 * Command for cleaning cache
 *
 * @api
 * @since 2.0.0
 */
class CacheCleanCommand extends AbstractCacheTypeManageCommand
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function performAction(array $cacheTypes)
    {
        $this->eventManager->dispatch('adminhtml_cache_flush_system');
        $this->cacheManager->clean($cacheTypes);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function getDisplayMessage()
    {
        return 'Cleaned cache types:';
    }
}

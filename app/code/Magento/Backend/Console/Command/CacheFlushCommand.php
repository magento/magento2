<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Console\Command;

/**
 * Command for flushing cache
 *
 * @api
 * @since 2.0.0
 */
class CacheFlushCommand extends AbstractCacheTypeManageCommand
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function configure()
    {
        $this->setName('cache:flush');
        $this->setDescription('Flushes cache storage used by cache type(s)');
        parent::configure();
    }

    /**
     * Flushes cache types
     *
     * @param array $cacheTypes
     * @return void
     * @since 2.0.0
     */
    protected function performAction(array $cacheTypes)
    {
        $this->eventManager->dispatch('adminhtml_cache_flush_all');
        $this->cacheManager->flush($cacheTypes);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function getDisplayMessage()
    {
        return 'Flushed cache types:';
    }
}

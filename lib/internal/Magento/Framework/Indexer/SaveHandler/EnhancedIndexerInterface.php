<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer\SaveHandler;

interface EnhancedIndexerInterface extends IndexerInterface
{
    /**
     * Disable stacked queries mode
     *
     * @return void
     */
    public function disableStackedActions(): void;

    /**
     * Activates stacked actions mode
     *
     * @return void
     */
    public function enableStackedActions(): void;

    /**
     * Run stacked queries
     *
     * @return void
     */
    public function triggerStackedActions(): void;
}

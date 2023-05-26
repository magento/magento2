<?php

namespace Magento\Framework\Indexer\SaveHandler;

interface EnhancedIndexerInterface extends IndexerInterface
{
    /**
     * @return void
     */
    public function disableStackedActions(): void;

    /**
     * @return void
     */
    public function enableStackedActions(): void;

    /**
     * @return void
     */
    public function triggerStackedActions(): void;
}

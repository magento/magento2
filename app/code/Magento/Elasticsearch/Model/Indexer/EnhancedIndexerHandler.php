<?php

namespace Magento\Elasticsearch\Model\Indexer;

class EnhancedIndexerHandler extends IndexerHandler
{
    /**
     * Disables stacked actions mode
     *
     * @return void
     */
    public function disableStackedActions(): void
    {
        $this->adapter->disableStackQueriesMode();
    }

    /**
     * Enables stacked actions mode
     *
     * @return void
     */
    public function enableStackedActions(): void
    {
        $this->adapter->enableStackQueriesMode();
    }

    /**
     * Runs stacked actions
     *
     * @return void
     * @throws \Exception
     */
    public function triggerStackedActions(): void
    {
        $this->adapter->triggerStackedQueries();
    }
}

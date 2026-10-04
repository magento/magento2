<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer\SaveHandler;

interface StackedActionsIndexerInterface
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

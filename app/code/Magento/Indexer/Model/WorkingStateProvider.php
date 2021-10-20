<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model;

use Magento\Indexer\Model\Indexer\StateFactory;
use Magento\Framework\Indexer\StateInterface;

/**
 * Provide actual working status of the indexer
 */
class WorkingStateProvider
{
    /**
     * @var StateFactory
     */
    private $stateFactory;

    /**
     * @param StateFactory $stateFactory
     */
    public function __construct(
        StateFactory $stateFactory
    ) {
        $this->stateFactory = $stateFactory;
    }

    /**
     * Execute user functions
     *
     * @param string $indexerId
     * @return bool
     */
    public function isWorking(string $indexerId) : bool
    {
        $state = $this->stateFactory->create();
        $state->loadByIndexer($indexerId);

        return $state->getStatus() === StateInterface::STATUS_WORKING;
    }
}

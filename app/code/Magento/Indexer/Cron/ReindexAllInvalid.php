<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Cron;

/**
 * Class \Magento\Indexer\Cron\ReindexAllInvalid
 *
 */
class ReindexAllInvalid
{
    /**
     * @var \Magento\Indexer\Model\Processor
     */
    protected $processor;

    /**
     * @param \Magento\Indexer\Model\Processor $processor
     */
    public function __construct(
        \Magento\Indexer\Model\Processor $processor
    ) {
        $this->processor = $processor;
    }

    /**
     * Regenerate indexes for all invalid indexers
     *
     * @return void
     */
    public function execute()
    {
        $this->processor->reindexAllInvalid();
    }
}

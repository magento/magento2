<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Cron;

use Magento\Indexer\Model\Indexer;

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

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Cron;

use Magento\Indexer\Model\Indexer;

/**
 * Class \Magento\Indexer\Cron\ReindexAllInvalid
 *
 * @since 2.0.0
 */
class ReindexAllInvalid
{
    /**
     * @var \Magento\Indexer\Model\Processor
     * @since 2.0.0
     */
    protected $processor;

    /**
     * @param \Magento\Indexer\Model\Processor $processor
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function execute()
    {
        $this->processor->reindexAllInvalid();
    }
}

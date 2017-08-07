<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Cron;

/**
 * Class \Magento\Indexer\Cron\ClearChangelog
 *
 */
class ClearChangelog
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
     * Clean indexer view changelogs
     *
     * @return void
     */
    public function execute()
    {
        $this->processor->clearChangelog();
    }
}

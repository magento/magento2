<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Cron;

/**
 * Class \Magento\Indexer\Cron\ClearChangelog
 *
 * @since 2.0.0
 */
class ClearChangelog
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
     * Clean indexer view changelogs
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->processor->clearChangelog();
    }
}

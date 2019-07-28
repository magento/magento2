<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\TestStep;

use Magento\Mtf\Util\Command\Cli\Indexer;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Run reindex process step.
 */
class ReindexStep implements TestStepInterface
{
    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * List of indexers to reindex
     *
     * @var array
     */
    private $indexerType;

    /**
     * @param Indexer $indexer
     * @param array $indexerType
     */
    public function __construct(
        Indexer $indexer,
        array $indexerType = []
    ) {
        $this->indexer = $indexer;
        $this->indexerType = $indexerType;
    }

    /**
     * Run reindex process.
     * All indexers will be refreshed in a case of empty $indexerType array.
     *
     * @return void
     */
    public function run()
    {
        $this->indexer->reindex($this->indexerType);
    }
}

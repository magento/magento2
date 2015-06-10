<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Processor;

use Magento\Indexer\Model\SourceInterface;
use Magento\Indexer\Model\SourcePool;

class Source
{
    /**
     * @var SourcePool
     */
    private $sourcePool;

    /**
     * @param SourcePool $sourcePool
     */
    public function __construct(SourcePool $sourcePool)
    {
        $this->sourcePool = $sourcePool;
    }

    /**
     * @param array $sourceNames
     * @return SourceInterface[]
     */
    public function process(array $sourceNames)
    {
        $sourceObjects = [];
        foreach ($sourceNames as $sourceName => $source) {
            $sourceObjects[$sourceName] = $this->sourcePool->get($source);
        }

        return $sourceObjects;
    }
}

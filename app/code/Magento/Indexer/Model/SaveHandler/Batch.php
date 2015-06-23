<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\SaveHandler;

class Batch
{
    private $n;

    /**
     * @param $n
     */
    public function __construct($n)
    {
        $this->n = $n;
    }

    /**
     * @param \Traversable $documents
     * @return \Generator
     */
    public function getItems(\Traversable $documents)
    {
        $i = 0;
        $batch = [];

        foreach ($documents as $documentName => $documentValue) {
            $batch[$documentName] = $documentValue;

            if ($i >= $this->n) {
                yield $batch;
                $i = 0;
                $batch = [];
            }
        }
        if (count($batch) > 0) {
            yield $batch;
        }
    }
}

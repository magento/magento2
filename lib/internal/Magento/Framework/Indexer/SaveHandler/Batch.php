<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\SaveHandler;

class Batch
{
    /**
     * @param \Traversable $documents
     * @param int $size
     * @return \Generator
     */
    public function getItems(\Traversable $documents, $size)
    {
        $i = 0;
        $batch = [];

        if (iterator_count($documents) == 0) {
            return [$batch];
        }

        foreach ($documents as $documentName => $documentValue) {
            $batch[$documentName] = $documentValue;
            if ($i++ >= $size) {
                return [$batch];
                $i = 0;
                $batch = [];
            }
        }
        if (count($batch) > 0) {
            return [$batch];
        }
    }
}

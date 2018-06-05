<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\SaveHandler;

class Batch
{
    /**
     * @param \Traversable $documents
     * @param int $size
     * @return array
     */
    public function getItems(\Traversable $documents, $size)
    {
        if (count($documents) == 0) {
            return [];
        }

        $i = 0;
        $batch = $items = [];
        foreach ($documents as $documentName => $documentValue) {
            $batch[$documentName] = $documentValue;
            if (++$i >= $size) {
                $items[] = $batch;
                $i = 0;
                $batch = [];
            }
        }
        if (count($batch) > 0) {
            $items[] = $batch;
        }
        return $items;
    }
}

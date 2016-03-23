<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock;

class QueryProcessorComposite implements QueryProcessorInterface
{
    /**
     * @var array
     */
    private $queryProcessors;

    /**
     * QueryProcessorPool constructor.
     * @param QueryProcessorInterface[] $queryProcessors
     */
    public function __construct(array $queryProcessors = [])
    {
        $this->queryProcessors = $queryProcessors;
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param null|array $entityIds
     * @param bool $usePrimaryTable
     * @return \Magento\Framework\DB\Select
     */
    public function processQuery(\Magento\Framework\DB\Select $select, $entityIds = null, $usePrimaryTable = false)
    {
        foreach ($this->queryProcessors as $queryProcessor) {
            $select = $queryProcessor->processQuery($select, $entityIds, $usePrimaryTable);
        }
        return $select;
    }
}

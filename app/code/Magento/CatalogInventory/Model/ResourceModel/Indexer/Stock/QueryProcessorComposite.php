<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock;

/**
 * Class \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\QueryProcessorComposite
 *
 * @since 2.1.0
 */
class QueryProcessorComposite implements QueryProcessorInterface
{
    /**
     * @var array
     * @since 2.1.0
     */
    private $queryProcessors;

    /**
     * QueryProcessorPool constructor.
     * @param QueryProcessorInterface[] $queryProcessors
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function processQuery(\Magento\Framework\DB\Select $select, $entityIds = null, $usePrimaryTable = false)
    {
        foreach ($this->queryProcessors as $queryProcessor) {
            $select = $queryProcessor->processQuery($select, $entityIds, $usePrimaryTable);
        }
        return $select;
    }
}

<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Indexer\Action;

use Magento\Framework\App\ResourceConnection\SourceProviderInterface;

class Entity extends Base
{
    /**
     * @var string
     */
    protected $tableAlias = 'e';

    /**
     * Prepare select query
     *
     * @param array|int|null $ids
     * @return SourceProviderInterface
     */
    protected function prepareDataSource(array $ids = [])
    {
        return !count($ids)
            ? $this->createResultCollection()
            : $this->createResultCollection()->addFieldToFilter($this->getPrimaryResource()->getIdFieldName(), $ids);
    }
}

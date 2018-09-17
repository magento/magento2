<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
            : $this->createResultCollection()->addFieldToFilter($this->getPrimaryResource()->getRowIdFieldName(), $ids);
    }
}

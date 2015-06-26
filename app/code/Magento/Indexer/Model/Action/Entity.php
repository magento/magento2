<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Action;

use Magento\Framework\DB\Select;
use Magento\Indexer\Model\HandlerInterface;

class Entity extends Base
{
    /**
     * Create select from indexer configuration
     * @param array|int|null $ids
     *
     * @return Select
     */
    protected function prepareDataSource($ids = null)
    {
        $collection = $this->getPrimaryResource();
        foreach ($this->getPrimaryFieldset()['fields'] as $field) {
            $handler = $field['handler'];
            /** @var HandlerInterface $handler */
            $handler->prepareSql($collection, $field);
        }
        $collection->addFieldToFilter($collection->getIdFieldName(), $ids);

        return $collection;
    }
}

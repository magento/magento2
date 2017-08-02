<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Action;

use Magento\Framework\App\ResourceConnection\SourceProviderInterface;

/**
 * Class \Magento\Framework\Indexer\Action\Entity
 *
 * @since 2.0.0
 */
class Entity extends Base
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $tableAlias = 'e';

    /**
     * Prepare select query
     *
     * @param array|int|null $ids
     * @return SourceProviderInterface
     * @since 2.0.0
     */
    protected function prepareDataSource(array $ids = [])
    {
        return !count($ids)
            ? $this->createResultCollection()
            : $this->createResultCollection()->addFieldToFilter($this->getPrimaryResource()->getIdFieldName(), $ids);
    }
}

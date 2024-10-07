<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\ResourceModel\SynonymGroup;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Search\Model\ResourceModel\SynonymGroup as ResourceSynonymGroup;
use Magento\Search\Model\SynonymGroup as ModelSynonymGroup;

/**
 * Collection for SynonymGroup
 * @api
 * @since 100.1.0
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     * @since 100.1.0
     */
    protected $_idFieldName = 'group_id';

    /**
     * Define resource model
     *
     * @return void
     * @since 100.1.0
     */
    protected function _construct()
    {
        $this->_init(
            ModelSynonymGroup::class,
            ResourceSynonymGroup::class
        );
    }
}

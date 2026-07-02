<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Search\Model\ResourceModel\SynonymGroup;

/**
 * Collection for SynonymGroup
 * @api
 * @since 100.1.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
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
            \Magento\Search\Model\SynonymGroup::class,
            \Magento\Search\Model\ResourceModel\SynonymGroup::class
        );
    }
}

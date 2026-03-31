<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Indexer\Model\ResourceModel\Indexer\State;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Indexer\Model\Indexer\State::class,
            \Magento\Indexer\Model\ResourceModel\Indexer\State::class
        );
    }
}

<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Indexer\Model\ResourceModel\Mview\View\State;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection implements
    \Magento\Framework\Mview\View\State\CollectionInterface
{
    /**
     * Collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Indexer\Model\Mview\View\State::class,
            \Magento\Indexer\Model\ResourceModel\Mview\View\State::class
        );
    }
}

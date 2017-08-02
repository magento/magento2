<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\ResourceModel\Indexer\State;

/**
 * Class \Magento\Indexer\Model\ResourceModel\Indexer\State\Collection
 *
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Collection initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Indexer\Model\Indexer\State::class,
            \Magento\Indexer\Model\ResourceModel\Indexer\State::class
        );
    }
}

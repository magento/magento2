<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\ResourceModel;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Indexer\Model\ResourceModel\AbstractResource;

class AbstractResourceStub extends AbstractResource
{
    /**
     * New DB Adapter
     *
     * @var bool
     */
    protected $_newIndexAdapter = false;

    /**
     * Resource initializations
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_category_flat', 'entity_id');
    }

    /**
     * Retrieve connection for write data
     *
     * @return AdapterInterface|false
     */
    protected function _getIndexAdapter()
    {
        if (!$this->_newIndexAdapter) {
            return parent::_getIndexAdapter();
        } else {
            return $this->_getConnection('new');
        }
    }

    /**
     * Change write adapter
     *
     * @param bool $newIndexAdapter
     */
    public function newIndexAdapter($newIndexAdapter = true)
    {
        $this->_newIndexAdapter = $newIndexAdapter;
    }
}

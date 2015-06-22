<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

use Magento\Framework\App\Resource as AppResource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

class SaveHandler implements SaveHandlerInterface
{
    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @param AppResource $resource
     */
    public function __construct(
        AppResource $resource
    ) {
        $this->connection = $resource->getConnection('write');
    }

    /**
     * Save
     *
     * @param Select $select
     * @param string $indexTable
     * @return void
     */
    public function save(Select $select, $indexTable)
    {
        $this->connection->query(
            $this->connection->insertFromSelect($select, $indexTable)
        );
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

use Magento\Framework\DB\Select;

interface SaveHandlerInterface
{
    /**
     * Save
     *
     * @param Select $select
     * @param string $indexTable
     * @return void
     */
    public function save(Select $select, $indexTable);
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Indexer\Scope;

/**
 * Provides a functionality to replace main index with its temporary representation
 * @todo refactoring it copy from catalog search module
 */
interface IndexSwitcherInterface
{
    /**
     * Switch current index with temporary index
     *
     * It will drop current index table and rename temporary index table to the current index table.
     *
     * @param array $dimensions
     * @param string $name of the indexer
     * @return void
     */
    public function switchIndex(array $dimensions, $index);
}

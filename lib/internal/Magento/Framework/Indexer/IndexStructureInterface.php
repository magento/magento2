<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

use Magento\Framework\Search\Request\Dimension;

/**
 * Indexer structure (schema) handler
 *
 * @api
 */
interface IndexStructureInterface
{
    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return void
     */
    public function delete($index, array $dimensions = []);

    /**
     * @param string $index
     * @param array $fields
     * @param Dimension[] $dimensions
     * @return void
     */
    public function create($index, array $fields, array $dimensions = []);
}

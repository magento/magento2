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
 * @since 2.0.0
 */
interface IndexStructureInterface
{
    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return void
     * @since 2.0.0
     */
    public function delete($index, array $dimensions = []);

    /**
     * @param string $index
     * @param array $fields
     * @param Dimension[] $dimensions
     * @return void
     * @since 2.0.0
     */
    public function create($index, array $fields, array $dimensions = []);
}

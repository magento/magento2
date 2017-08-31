<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Indexer;

/**
 * Resolve index name by IndexName object
 * @api
 */
interface IndexNameResolverInterface
{
    /**
     * @param IndexName $indexName
     * @return string
     */
    public function resolveName(IndexName $indexName): string;
}

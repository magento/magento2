<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;

/**
 * Resolve total records count.
 */
interface TotalRecordsResolverInterface
{
    /**
     * Resolve total records.
     *
     * @return int
     */
    public function resolve(): ?int;
}

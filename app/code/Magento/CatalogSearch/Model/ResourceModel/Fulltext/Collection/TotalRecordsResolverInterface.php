<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;

/**
 * Resolve total records count.
 *
 * @api
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

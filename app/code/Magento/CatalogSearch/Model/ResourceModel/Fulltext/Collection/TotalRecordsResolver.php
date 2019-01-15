<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;

/**
 * Resolve total records count.
 */
class TotalRecordsResolver implements TotalRecordsResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(): ?int
    {
        return null;
    }
}

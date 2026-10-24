<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Export\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogImportExport\Model\Export\ProductFilterInterface;

/**
 * Website filter for products export
 */
class WebsiteFilter implements ProductFilterInterface
{
    private const WEBSITE_ID = 'website_id';
    private const WEBSITE_IDS = 'website_ids';

    /**
     * @inheritDoc
     */
    public function filter(Collection $collection, array $filters): Collection
    {
        if (!isset($filters[self::WEBSITE_ID]) && !isset($filters[self::WEBSITE_IDS])) {
            return $collection;
        }

        $collection->addWebsiteFilter($filters[self::WEBSITE_IDS] ?? $filters[self::WEBSITE_ID]);
        $collection->setFlag(self::WEBSITE_ID . '_filter_applied');
        $collection->setFlag(self::WEBSITE_IDS . '_filter_applied');

        return $collection;
    }
}

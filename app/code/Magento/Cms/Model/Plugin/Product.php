<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Model\Plugin;

use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Cms\Model\Page;

/**
 * Cleaning no-route page cache for the product details page after enabling product that is not assigned to a category
 */
class Product
{
    /**
     * @var Page
     */
    private $page;

    /**
     * @param Page $page
     */
    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    /**
     * After get identities
     *
     * @param CatalogProduct $product
     * @param array $identities
     * @return array
     */
    public function afterGetIdentities(CatalogProduct $product, array $identities)
    {
        if ($product->getOrigData('status') > $product->getData('status')) {
            if (empty($product->getCategoryIds())) {
                $noRoutePage = $this->page->load(Page::NOROUTE_PAGE_ID);
                $noRoutePageId = $noRoutePage->getId();
                $identities[] = Page::CACHE_TAG . '_' . $noRoutePageId;
            }
        }

        return array_unique($identities);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Model\ItemResolver;

use Magento\Sitemap\Model\SitemapItemInterface;

/**
 * Sitemap item resolver interface
 *
 * @api
 */
interface ItemResolverInterface
{
    /**
     * Get sitemap items
     *
     * @param int $storeId
     * @return SitemapItemInterface[]
     */
    public function getItems($storeId);
}

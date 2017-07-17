<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Model;

/**
 * Sitemap item resolver interface
 *
 * @api
 */
interface SitemapItemResolverInterface
{
    /**
     * Get sitemap items
     *
     * @param int $storeId
     * @return SitemapItemInterface[]
     */
    public function getItems($storeId);
}
<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Model\ItemProvider;

use Magento\Sitemap\Model\SitemapItemInterface;

/**
 * Sitemap item provider interface
 *
 * @api
 * @since 100.3.0
 */
interface ItemProviderInterface
{
    /**
     * Get sitemap items
     *
     * @param int $storeId
     * @return SitemapItemInterface[]
     * @since 100.3.0
     */
    public function getItems($storeId);
}

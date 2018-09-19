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
 */
interface ItemProviderInterface
{
    /**
     * Get sitemap items
     *
     * @param int $storeId
     * @return SitemapItemInterface[]
     */
    public function getItems($storeId);
}

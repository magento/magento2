<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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

<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Api\Data;

/**
 * Interface SitemapSearchResultsInterface
 * @package Magento\Sitemap\Api\Data
 */
interface SitemapSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get sitemap list
     *
     * @return \Magento\Sitemap\Api\Data\SitemapInterface[]
     */
    public function getItems();

    /**
     * Set sitemap list
     *
     * @api
     * @param \Magento\Sitemap\Api\Data\SitemapInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

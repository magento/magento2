<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model;

/**
 * Representation of sitemap item
 *
 * @api
 */
interface SitemapItemInterface
{

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl();

    /**
     * Get priority
     *
     * @return string
     */
    public function getPriority();

    /**
     * Get change frequency
     *
     * @return string
     */
    public function getChangeFrequency();

    /**
     * Get images
     *
     * @return array|null
     */
    public function getImages();

    /**
     * Get last update date
     *
     * @return string|null
     */
    public function getUpdatedAt();
}

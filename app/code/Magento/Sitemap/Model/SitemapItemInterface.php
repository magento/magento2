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
 * @since 100.3.0
 */
interface SitemapItemInterface
{

    /**
     * Get url
     *
     * @return string
     * @since 100.3.0
     */
    public function getUrl();

    /**
     * Get priority
     *
     * @return string
     * @since 100.3.0
     */
    public function getPriority();

    /**
     * Get change frequency
     *
     * @return string
     * @since 100.3.0
     */
    public function getChangeFrequency();

    /**
     * Get images
     *
     * @return array|null
     * @since 100.3.0
     */
    public function getImages();

    /**
     * Get last update date
     *
     * @return string|null
     * @since 100.3.0
     */
    public function getUpdatedAt();
}

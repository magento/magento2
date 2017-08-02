<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Rss;

/**
 * @api
 * @since 2.0.0
 */
interface DataProviderInterface
{
    /**
     * Check if RSS feed allowed
     *
     * @return mixed
     * @since 2.0.0
     */
    public function isAllowed();

    /**
     * Get RSS feed items
     *
     * @return array
     * @since 2.0.0
     */
    public function getRssData();

    /**
     * @return string
     * @since 2.0.0
     */
    public function getCacheKey();

    /**
     * @return int
     * @since 2.0.0
     */
    public function getCacheLifetime();

    /**
     * Get information about all feeds this Data Provider is responsible for
     *
     * @return array
     * @since 2.0.0
     */
    public function getFeeds();

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isAuthRequired();
}

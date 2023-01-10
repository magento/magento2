<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model;

/**
 * Sitemap config reader interface
 *
 * @api
 * @since 100.3.0
 */
interface SitemapConfigReaderInterface
{
    /**
     * Get enable Submission to Robots.txt
     *
     * @param int $storeId
     * @return int
     * @since 100.3.0
     */
    public function getEnableSubmissionRobots($storeId);

    /**
     * Get maximum sitemap.xml file size in bytes
     *
     * @param int $storeId
     * @return int
     * @since 100.3.0
     */
    public function getMaximumFileSize($storeId);

    /**
     * Get maximum sitemap.xml URLs number
     *
     * @param int $storeId
     * @return int
     * @since 100.3.0
     */
    public function getMaximumLinesNumber($storeId);

    /**
     * Get product image include policy
     *
     * @param int $storeId
     * @return string
     * @since 100.3.0
     */
    public function getProductImageIncludePolicy($storeId);

    /**
     * Get list valid paths for generate a sitemap XML file
     *
     * @return string[]
     * @since 100.3.0
     */
    public function getValidPaths();
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sitemap\Api\Data;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
interface SitemapInterface
{

    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const SITEMAP_TIME      = 'sitemap_time';
    const SITEMAP_TYPE      = 'sitemap_type';
    const SITEMAP_FILENAME  = 'sitemap_filename';
    const SITEMAP_PATH      = 'sitemap_path';
    const STORE_ID          = 'store_id';
    /**#@-*/


    /**
     * Identifier getter
     *
     * @return int|null
     */
    public function getId();

    /**
     * Identifier setter
     *
     * @param int $value
     * @return $this
     */
    public function setId($id);

    /**
     * Get sitemap.xml URL according to all config options
     *
     * @param string $sitemapPath
     * @param string $sitemapFileName
     * @return string
     */
    public function getSitemapUrl($sitemapPath, $sitemapFileName);


    /**
     * Get sitemap type
     *
     * @return string|null
     */
    public function getSitemapType();

    /**
     * @param string $type
     * @return $this
     */
    public function setSitemapType(string $type): SitemapInterface;

    /**
     * Get sitemap filename
     *
     * @return string|null
     */
    public function getSitemapFilename();

    /**
     * Set sitemap filename
     *
     * @return $this
     */
    public function setSitemapFilename(string $filename): SitemapInterface;

    /**
     * Get sitemap path
     *
     * @return string|null
     */
    public function getSitemapPath();

    /**
     * Set sitemap path
     *
     * @param string $path
     * @return $this
     */
    public function setSitemapPath(string $path): SitemapInterface;

    /**
     * Get sitemap time
     *
     * @return string|null
     */
    public function getSitemapTime();

    /**
     * Set sitemap time
     *
     * @param string $datetime
     * @return $this
     */
    public function setSitemapTime(string $datetime): SitemapInterface;

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int $id
     * @return SitemapInterface
     */
    public function setStoreId(int $id): SitemapInterface;

}
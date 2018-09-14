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
     * @return string
     */
    public function getSitemapType();

    /**
     * @param string $type
     * @return mixed
     */
    public function setSitemapType(string $type): SitemapInterface;

    /**
     * @return mixed
     */
    public function getSitemapFilename();

    /**
     * @return mixed
     */
    public function setSitemapFilename(string $filename): SitemapInterface;

    /**
     * @return mixed
     */
    public function getSitemapPath();

    /**
     * @param string $path
     * @return mixed
     */
    public function setSitemapPath(string $path): SitemapInterface;

    /**
     * @return mixed
     */
    public function getSitemapTime();

    /**
     * @param string $datetime
     * @return mixed
     */
    public function setSitemapTime(string $datetime): SitemapInterface;

    /**
     * @return mixed
     */
    public function getStoreId();

    /**
     * @param int $id
     * @return mixed
     */
    public function setStoreId(int $id): SitemapInterface;

    /**
     * Generate XML file
     *
     * @see http://www.sitemaps.org/protocol.html
     *
     * @return $this
     */
    public function generateXml();

}
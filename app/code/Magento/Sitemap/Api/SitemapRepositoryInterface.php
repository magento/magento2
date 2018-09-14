<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Api;


use Magento\Sitemap\Api\Data\SitemapInterface;

/**
 * Interface SitemapRepositoryInterface
 * @package Magento\Sitemap\Model\Api
 */
interface SitemapRepositoryInterface
{
    /**
     * @param $sitemapId
     * @return SitemapInterface
     */
    public function getById($sitemapId): SitemapInterface;

    /**
     * @return array
     */
    public function getList(): array;

    /**
     * @param SitemapInterface $sitemap
     * @return mixed
     */
    public function save(SitemapInterface $sitemap);

    /**
     * @param SitemapInterface $sitemap
     * @return mixed
     */
    public function delete(SitemapInterface $sitemap);
}
<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Api;


use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sitemap\Api\Data\SitemapInterface;
use Magento\Sitemap\Api\Data\SitemapSearchResultsInterface;

/**
 * Interface SitemapRepositoryInterface
 * @package Magento\Sitemap\Model\Api
 */
interface SitemapRepositoryInterface
{
    /**
     * Get single sitemap by id
     *
     * @param int $sitemapId
     * @throws NoSuchEntityException
     * @return SitemapInterface
     */
    public function getById($sitemapId): SitemapInterface;

    /**
     * Fetch list of sitemaps according to search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @throws NoSuchEntityException
     * @return SitemapSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SitemapSearchResultsInterface;

    /**
     * Save sitemap instance
     *
     * @param SitemapInterface $sitemap
     * @throws CouldNotSaveException
     * @return int
     */
    public function save(SitemapInterface $sitemap): int;

    /**
     * Delete passed sitemap
     *
     * @param SitemapInterface $sitemap
     * @throws CouldNotDeleteException
     * @return boolean
     */
    public function delete(SitemapInterface $sitemap): bool;
}
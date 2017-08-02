<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * CMS page CRUD interface.
 * @api
 * @since 2.0.0
 */
interface PageRepositoryInterface
{
    /**
     * Save page.
     *
     * @param \Magento\Cms\Api\Data\PageInterface $page
     * @return \Magento\Cms\Api\Data\PageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function save(\Magento\Cms\Api\Data\PageInterface $page);

    /**
     * Retrieve page.
     *
     * @param int $pageId
     * @return \Magento\Cms\Api\Data\PageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getById($pageId);

    /**
     * Retrieve pages matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Cms\Api\Data\PageSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete page.
     *
     * @param \Magento\Cms\Api\Data\PageInterface $page
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function delete(\Magento\Cms\Api\Data\PageInterface $page);

    /**
     * Delete page by ID.
     *
     * @param int $pageId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function deleteById($pageId);
}

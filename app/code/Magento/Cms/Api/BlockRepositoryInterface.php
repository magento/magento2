<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * CMS block CRUD interface.
 * @api
 * @since 2.0.0
 */
interface BlockRepositoryInterface
{
    /**
     * Save block.
     *
     * @param \Magento\Cms\Api\Data\BlockInterface $block
     * @return \Magento\Cms\Api\Data\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function save(Data\BlockInterface $block);

    /**
     * Retrieve block.
     *
     * @param int $blockId
     * @return \Magento\Cms\Api\Data\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getById($blockId);

    /**
     * Retrieve blocks matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Cms\Api\Data\BlockSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete block.
     *
     * @param \Magento\Cms\Api\Data\BlockInterface $block
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function delete(Data\BlockInterface $block);

    /**
     * Delete block by ID.
     *
     * @param int $blockId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function deleteById($blockId);
}

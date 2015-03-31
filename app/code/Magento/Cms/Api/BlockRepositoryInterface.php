<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * CMS block CRUD interface.
 */
interface BlockRepositoryInterface
{
    /**
     * Save block.
     *
     * @param Data\BlockInterface $block
     * @return Data\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\BlockInterface $block);

    /**
     * Retrieve block.
     *
     * @param int $blockId
     * @return Data\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($blockId);

    /**
     * Retrieve blocks matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete block.
     *
     * @param Data\BlockInterface $block
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\BlockInterface $block);

    /**
     * Delete block by ID.
     *
     * @param int $blockId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($blockId);
}

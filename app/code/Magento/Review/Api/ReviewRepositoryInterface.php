<?php
namespace Magento\Review\Api;

use Magento\Review\Model\ResourceModel\Review\Collection;

interface ReviewRepositoryInterface
{

    /**
     * Gets a review by it's id.
     * 
     * @param int $reviewId
     * @return \Magento\Review\Api\Data\ReviewInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($reviewId);
    
    /**
     * Returns list of reviews based on the given searchCriteria.
     * 
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Review\Api\Data\ReviewSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Saves a review.
     * 
     * @param \Magento\Review\Api\Data\ReviewInterface $review
     * @return \Magento\Review\Api\Data\ReviewInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Magento\Review\Api\Data\ReviewInterface $review);

    /**
     * Deletes a review.
     *
     * @param int $reviewId
     * @return \Magento\Review\Api\Data\ReviewInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete($reviewId);
}
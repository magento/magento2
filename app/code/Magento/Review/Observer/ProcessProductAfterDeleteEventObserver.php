<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Review\Observer\ProcessProductAfterDeleteEventObserver
 *
 * @since 2.0.0
 */
class ProcessProductAfterDeleteEventObserver implements ObserverInterface
{
    /**
     * Review resource model
     *
     * @var \Magento\Review\Model\ResourceModel\Review
     * @since 2.0.0
     */
    protected $_resourceReview;

    /**
     * @var \Magento\Review\Model\ResourceModel\Rating
     * @since 2.0.0
     */
    protected $_resourceRating;

    /**
     * @param \Magento\Review\Model\ResourceModel\Review $resourceReview
     * @param \Magento\Review\Model\ResourceModel\Rating $resourceRating
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Review\Model\ResourceModel\Review $resourceReview,
        \Magento\Review\Model\ResourceModel\Rating $resourceRating
    ) {
        $this->_resourceReview = $resourceReview;
        $this->_resourceRating = $resourceRating;
    }

    /**
     * Cleanup product reviews after product delete
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  $this
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $eventProduct = $observer->getEvent()->getProduct();
        if ($eventProduct && $eventProduct->getId()) {
            $this->_resourceReview->deleteReviewsByProductId($eventProduct->getId());
            $this->_resourceRating->deleteAggregatedRatingsByProductId($eventProduct->getId());
        }

        return $this;
    }
}

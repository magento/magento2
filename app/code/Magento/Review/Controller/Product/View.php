<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Product;

use Magento\Review\Controller\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Model\Review;

class View extends ProductController
{
    /**
     * Load review model with data by passed id.
     * Return false if review was not loaded or review is not approved.
     *
     * @param int $reviewId
     * @return bool|Review
     */
    protected function loadReview($reviewId)
    {
        if (!$reviewId) {
            return false;
        }
        /** @var \Magento\Review\Model\Review $review */
        $review = $this->reviewFactory->create()->load($reviewId);
        if (!$review->getId()
            || !$review->isApproved()
            || !$review->isAvailableOnStore($this->storeManager->getStore())
        ) {
            return false;
        }
        $this->coreRegistry->register('current_review', $review);
        return $review;
    }

    /**
     * Show details of one review
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $review = $this->loadReview((int)$this->getRequest()->getParam('id'));
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        if (!$review) {
            $resultForward->forward('noroute');
            return $resultForward;
        }

        $product = $this->loadProduct($review->getEntityPkValue());
        if (!$product) {
            $resultForward->forward('noroute');
            return $resultForward;
        }
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }
}

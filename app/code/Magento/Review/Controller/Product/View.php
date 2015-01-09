<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Product;

use Magento\Review\Model\Review;

class View extends \Magento\Review\Controller\Product
{
    /**
     * Load review model with data by passed id.
     * Return false if review was not loaded or review is not approved.
     *
     * @param int $reviewId
     * @return bool|Review
     */
    protected function _loadReview($reviewId)
    {
        if (!$reviewId) {
            return false;
        }

        $review = $this->_reviewFactory->create()->load($reviewId);
        /* @var $review Review */
        if (!$review->getId()
            || !$review->isApproved()
            || !$review->isAvailableOnStore($this->_storeManager->getStore())
        ) {
            return false;
        }

        $this->_coreRegistry->register('current_review', $review);

        return $review;
    }

    /**
     * Show details of one review
     *
     * @return void
     */
    public function execute()
    {
        $review = $this->_loadReview((int)$this->getRequest()->getParam('id'));
        if (!$review) {
            $this->_forward('noroute');
            return;
        }

        $product = $this->_loadProduct($review->getEntityPkValue());
        if (!$product) {
            $this->_forward('noroute');
            return;
        }

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}

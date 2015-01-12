<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Product;

use Magento\Review\Model\Review;

class Post extends \Magento\Review\Controller\Product
{
    /**
     * Submit new review action
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
            return;
        }

        $data = $this->_reviewSession->getFormData(true);
        if ($data) {
            $rating = [];
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data = $this->getRequest()->getPost();
            $rating = $this->getRequest()->getParam('ratings', []);
        }

        if (($product = $this->_initProduct()) && !empty($data)) {
            $review = $this->_reviewFactory->create()->setData($data);
            /* @var $review Review */

            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $review->setEntityId(
                        $review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE)
                    )->setEntityPkValue(
                        $product->getId()
                    )->setStatusId(
                        Review::STATUS_PENDING
                    )->setCustomerId(
                        $this->_customerSession->getCustomerId()
                    )->setStoreId(
                        $this->_storeManager->getStore()->getId()
                    )->setStores(
                        [$this->_storeManager->getStore()->getId()]
                    )->save();

                    foreach ($rating as $ratingId => $optionId) {
                        $this->_ratingFactory->create()->setRatingId(
                            $ratingId
                        )->setReviewId(
                            $review->getId()
                        )->setCustomerId(
                            $this->_customerSession->getCustomerId()
                        )->addOptionVote(
                            $optionId,
                            $product->getId()
                        );
                    }

                    $review->aggregate();
                    $this->messageManager->addSuccess(__('Your review has been accepted for moderation.'));
                } catch (\Exception $e) {
                    $this->_reviewSession->setFormData($data);
                    $this->messageManager->addError(__('We cannot post the review.'));
                }
            } else {
                $this->_reviewSession->setFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $this->messageManager->addError($errorMessage);
                    }
                } else {
                    $this->messageManager->addError(__('We cannot post the review.'));
                }
            }
        }

        $redirectUrl = $this->_reviewSession->getRedirectUrl(true);
        if ($redirectUrl) {
            $this->getResponse()->setRedirect($redirectUrl);
            return;
        }
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }
}

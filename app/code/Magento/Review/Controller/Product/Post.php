<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Review\Controller\Product;

use \Magento\Review\Model\Review;

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
            $rating = array();
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data = $this->getRequest()->getPost();
            $rating = $this->getRequest()->getParam('ratings', array());
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
                        array($this->_storeManager->getStore()->getId())
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

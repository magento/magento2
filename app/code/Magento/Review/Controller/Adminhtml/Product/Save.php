<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

class Save extends \Magento\Review\Controller\Adminhtml\Product
{
    /**
     * @return mixed
     */
    public function execute()
    {
        if (($data = $this->getRequest()->getPost()) && ($reviewId = $this->getRequest()->getParam('id'))) {
            $review = $this->_reviewFactory->create()->load($reviewId);
            if (!$review->getId()) {
                $this->messageManager->addError(__('The review was removed by another user or does not exist.'));
            } else {
                try {
                    $review->addData($data)->save();

                    $arrRatingId = $this->getRequest()->getParam('ratings', []);
                    $votes = $this->_objectManager->create(
                        'Magento\Review\Model\Rating\Option\Vote'
                    )->getResourceCollection()->setReviewFilter(
                        $reviewId
                    )->addOptionInfo()->load()->addRatingOptions();
                    foreach ($arrRatingId as $ratingId => $optionId) {
                        if ($vote = $votes->getItemByColumnValue('rating_id', $ratingId)) {
                            $this->_ratingFactory->create(
                            )->setVoteId(
                                $vote->getId()
                            )->setReviewId(
                                $review->getId()
                            )->updateOptionVote(
                                $optionId
                            );
                        } else {
                            $this->_ratingFactory->create(
                            )->setRatingId(
                                $ratingId
                            )->setReviewId(
                                $review->getId()
                            )->addOptionVote(
                                $optionId,
                                $review->getEntityPkValue()
                            );
                        }
                    }

                    $review->aggregate();

                    $this->messageManager->addSuccess(__('You saved the review.'));
                } catch (\Magento\Framework\Model\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while saving this review.'));
                }
            }

            $nextId = (int)$this->getRequest()->getParam('next_item');
            $url = $this->getUrl($this->getRequest()->getParam('ret') == 'pending' ? '*/*/pending' : '*/*/');
            if ($nextId) {
                $url = $this->getUrl('review/*/edit', ['id' => $nextId]);
            }
            return $this->getResponse()->setRedirect($url);
        }
        $this->_redirect('review/*/');
    }
}

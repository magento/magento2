<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Review\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Review\Model\Review;

/**
 * Save Review action.
 */
class Save extends ProductController implements HttpPostActionInterface
{
    /**
     * @var Review
     */
    private $review;

    /**
     * Save Review action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (($data = $this->getRequest()->getPostValue()) && ($reviewId = $this->getRequest()->getParam('id'))) {
            $review = $this->getModel();
            if (!$review->getId()) {
                $this->messageManager->addError(__('The review was removed by another user or does not exist.'));
            } else {
                try {
                    $review->addData($data)->save();

                    $arrRatingId = $this->getRequest()->getParam('ratings', []);
                    /** @var \Magento\Review\Model\Rating\Option\Vote $votes */
                    $votes = $this->_objectManager->create(\Magento\Review\Model\Rating\Option\Vote::class)
                        ->getResourceCollection()
                        ->setReviewFilter($reviewId)
                        ->addOptionInfo()
                        ->load()
                        ->addRatingOptions();
                    foreach ($arrRatingId as $ratingId => $optionId) {
                        if ($vote = $votes->getItemByColumnValue('rating_id', $ratingId)) {
                            $this->ratingFactory->create()
                                ->setVoteId($vote->getId())
                                ->setReviewId($review->getId())
                                ->updateOptionVote($optionId);
                        } else {
                            $this->ratingFactory->create()
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->addOptionVote($optionId, $review->getEntityPkValue());
                        }
                    }

                    $review->aggregate();

                    $this->messageManager->addSuccess(__('You saved the review.'));
                } catch (LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while saving this review.'));
                }
            }

            $nextId = (int)$this->getRequest()->getParam('next_item');
            if ($nextId) {
                $resultRedirect->setPath(
                    'review/*/edit',
                    [
                        'id' => $nextId,
                        'ret' => $this->getRequest()
                            ->getParam('ret'),
                    ]
                );
            } elseif ($this->getRequest()->getParam('ret') == 'pending') {
                $resultRedirect->setPath('review/*/pending');
            } else {
                $resultRedirect->setPath('*/*/');
            }
            $productId = (int)$this->getRequest()->getParam('productId');
            if ($productId) {
                $resultRedirect->setPath("catalog/product/edit/id/$productId");
            }
            $customerId = (int)$this->getRequest()->getParam('customerId');
            if ($customerId) {
                $resultRedirect->setPath("customer/index/edit/id/$customerId");
            }
            return $resultRedirect;
        }
        $resultRedirect->setPath('review/*/');
        return $resultRedirect;
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        if (parent::_isAllowed()) {
            return true;
        }

        if (!$this->_authorization->isAllowed('Magento_Review::pending')) {
            return  false;
        }

        if ($this->getModel()->getStatusId() != Review::STATUS_PENDING) {
            $this->messageManager->addErrorMessage(
                __(
                    'You don’t have permission to perform this operation.'
                    . ' The selected review must be in Pending Status.'
                )
            );

            return false;
        }

        return true;
    }

    /**
     * Returns requested model.
     *
     * @return Review
     */
    private function getModel(): Review
    {
        if (!$this->review) {
            $this->review = $this->reviewFactory->create()
                ->load($this->getRequest()->getParam('id', false));
        }

        return $this->review;
    }
}

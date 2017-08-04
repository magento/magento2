<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

use Magento\Review\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\Store;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class \Magento\Review\Controller\Adminhtml\Product\Post
 *
 */
class Post extends ProductController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id', false);
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($data = $this->getRequest()->getPostValue()) {
            /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
            $storeManager = $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
            if ($storeManager->hasSingleStore()) {
                $data['stores'] = [
                    $storeManager->getStore(true)->getId(),
                ];
            } elseif (isset($data['select_stores'])) {
                $data['stores'] = $data['select_stores'];
            }
            $review = $this->reviewFactory->create()->setData($data);
            try {
                $review->setEntityId(1) // product
                    ->setEntityPkValue($productId)
                    ->setStoreId(Store::DEFAULT_STORE_ID)
                    ->setStatusId($data['status_id'])
                    ->setCustomerId(null)//null is for administrator only
                    ->save();

                $arrRatingId = $this->getRequest()->getParam('ratings', []);
                foreach ($arrRatingId as $ratingId => $optionId) {
                    $this->ratingFactory->create()
                        ->setRatingId($ratingId)
                        ->setReviewId($review->getId())
                        ->addOptionVote($optionId, $productId);
                }

                $review->aggregate();

                $this->messageManager->addSuccess(__('You saved the review.'));
                if ($this->getRequest()->getParam('ret') == 'pending') {
                    $resultRedirect->setPath('review/*/pending');
                } else {
                    $resultRedirect->setPath('review/*/');
                }
                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving this review.'));
            }
        }
        $resultRedirect->setPath('review/*/');
        return $resultRedirect;
    }
}

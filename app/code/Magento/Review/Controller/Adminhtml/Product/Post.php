<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

class Post extends \Magento\Review\Controller\Adminhtml\Product
{
    /**
     * @return void
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id', false);

        if ($data = $this->getRequest()->getPost()) {
            /** @var \Magento\Store\Model\StoreManagerInterface $storeManagerInterface */
            $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
            if ($storeManager->hasSingleStore()) {
                $data['stores'] = [
                    $storeManager->getStore(true)->getId(),
                ];
            } elseif (isset($data['select_stores'])) {
                $data['stores'] = $data['select_stores'];
            }

            $review = $this->_reviewFactory->create()->setData($data);

            try {
                $review->setEntityId(1) // product
                    ->setEntityPkValue($productId)
                    ->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID)
                    ->setStatusId($data['status_id'])
                    ->setCustomerId(null)//null is for administrator only
                    ->save();

                $arrRatingId = $this->getRequest()->getParam('ratings', []);
                foreach ($arrRatingId as $ratingId => $optionId) {
                    $this->_ratingFactory->create(
                    )->setRatingId(
                        $ratingId
                    )->setReviewId(
                        $review->getId()
                    )->addOptionVote(
                        $optionId,
                        $productId
                    );
                }

                $review->aggregate();

                $this->messageManager->addSuccess(__('You saved the review.'));
                if ($this->getRequest()->getParam('ret') == 'pending') {
                    $this->getResponse()->setRedirect($this->getUrl('review/*/pending'));
                } else {
                    $this->getResponse()->setRedirect($this->getUrl('review/*/'));
                }

                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('An error occurred while saving review.'));
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('review/*/'));
        return;
    }
}

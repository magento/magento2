<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

class MassDelete extends \Magento\Review\Controller\Adminhtml\Product
{
    /**
     * @return void
     */
    public function execute()
    {
        $reviewsIds = $this->getRequest()->getParam('reviews');

        if (!is_array($reviewsIds)) {
            $this->messageManager->addError(__('Please select review(s).'));
        } else {
            try {
                foreach ($reviewsIds as $reviewId) {
                    $model = $this->_reviewFactory->create()->load($reviewId);
                    $model->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($reviewsIds))
                );
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('An error occurred while deleting record(s).'));
            }
        }

        $this->_redirect('review/*/' . $this->getRequest()->getParam('ret', 'index'));
    }
}

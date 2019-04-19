<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

use Magento\Review\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;

class MassUpdateStatus extends ProductController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new \Magento\Framework\Exception\NotFoundException(__('Page not found.'));
        }

        $reviewsIds = $this->getRequest()->getParam('reviews');
        if (!is_array($reviewsIds)) {
            $this->messageManager->addError(__('Please select review(s).'));
        } else {
            try {
                $status = $this->getRequest()->getParam('status');
                foreach ($reviewsIds as $reviewId) {
                    $model = $this->reviewFactory->create()->load($reviewId);
                    $model->setStatusId($status)->save()->aggregate();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been updated.', count($reviewsIds))
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while updating these review(s).')
                );
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('review/*/' . $this->getRequest()->getParam('ret', 'index'));
        return $resultRedirect;
    }
}

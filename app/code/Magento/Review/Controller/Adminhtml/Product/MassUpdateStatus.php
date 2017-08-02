<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

use Magento\Review\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\Review\Controller\Adminhtml\Product\MassUpdateStatus
 *
 * @since 2.0.0
 */
class MassUpdateStatus extends ProductController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
     */
    public function execute()
    {
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

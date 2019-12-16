<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Review\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Model\Review;

/**
 * Delete review action.
 */
class Delete extends ProductController implements HttpPostActionInterface
{
    /**
     * @var Review
     */
    private $model;

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $reviewId = $this->getRequest()->getParam('id', false);
        try {
            $this->getModel()->aggregate()->delete();

            $this->messageManager->addSuccess(__('The review has been deleted.'));
            if ($this->getRequest()->getParam('ret') == 'pending') {
                $resultRedirect->setPath('review/*/pending');
            } else {
                $resultRedirect->setPath('review/*/');
            }
            return $resultRedirect;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong  deleting this review.'));
        }

        return $resultRedirect->setPath('review/*/edit/', ['id' => $reviewId]);
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
        if ($this->model === null) {
            $this->model = $this->reviewFactory->create()
                ->load($this->getRequest()->getParam('id', false));
        }

        return $this->model;
    }
}

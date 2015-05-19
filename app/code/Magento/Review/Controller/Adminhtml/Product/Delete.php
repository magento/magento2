<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

use Magento\Review\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;

class Delete extends ProductController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $reviewId = $this->getRequest()->getParam('id', false);
        $this->reviewFactory->create()->setId($reviewId)->aggregate()->delete();
        $this->messageManager->addSuccess(__('The review has been deleted.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($this->getRequest()->getParam('ret') == 'pending') {
            $resultRedirect->setPath('review/*/pending');
        } else {
            $resultRedirect->setPath('review/*/');
        }
        return $resultRedirect;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function getDefaultResult()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('review/*/edit/', ['id' => $this->getRequest()->getParam('id', false)]);
        return $resultRedirect;
    }
}

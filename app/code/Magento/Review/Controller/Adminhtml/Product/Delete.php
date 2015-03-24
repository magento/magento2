<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

class Delete extends \Magento\Review\Controller\Adminhtml\Product
{
    /**
     * @var int
     */
    protected $reviewId;

    /**
     * @return void
     */
    public function execute()
    {
        $this->reviewId = $this->getRequest()->getParam('id', false);
        $this->_reviewFactory->create()->setId($this->reviewId)->aggregate()->delete();

        $this->messageManager->addSuccess(__('The review has been deleted.'));
        if ($this->getRequest()->getParam('ret') == 'pending') {
            $this->getResponse()->setRedirect($this->getUrl('review/*/pending'));
        } else {
            $this->getResponse()->setRedirect($this->getUrl('review/*/'));
        }
        return;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function getDefaultRedirect()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('review/*/edit/', ['id' => $this->reviewId]);
    }
}

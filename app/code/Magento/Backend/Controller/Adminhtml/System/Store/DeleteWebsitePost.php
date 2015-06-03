<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;

class DeleteWebsitePost extends \Magento\Backend\Controller\Adminhtml\System\Store
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $itemId = $this->getRequest()->getParam('item_id');
        $model = $this->_objectManager->create('Magento\Store\Model\Website');
        $model->load($itemId);

        /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
        $redirectResult = $this->resultRedirectFactory->create();

        if (!$model) {
            $this->messageManager->addError(__('Something went wrong. Please try again.'));
            return $redirectResult->setPath('adminhtml/*/');
        }
        if (!$model->isCanDelete()) {
            $this->messageManager->addError(__('This website cannot be deleted.'));
            return $redirectResult->setPath('adminhtml/*/editWebsite', ['website_id' => $model->getId()]);
        }

        if (!$this->_backupDatabase()) {
            return $redirectResult->setPath('*/*/editWebsite', ['website_id' => $itemId]);
        }

        $model->delete();
        $this->messageManager->addSuccess(__('You deleted the website.'));
        return $redirectResult->setPath('adminhtml/*/');
    }
}

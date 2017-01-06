<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;

class DeleteStore extends \Magento\Backend\Controller\Adminhtml\System\Store
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $itemId = $this->getRequest()->getParam('item_id', null);
        if (!($model = $this->_objectManager->create(\Magento\Store\Model\Store::class)->load($itemId))) {
            $this->messageManager->addError(__('Something went wrong. Please try again.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
            $redirectResult = $this->resultRedirectFactory->create();
            return $redirectResult->setPath('adminhtml/*/');
        }
        if (!$model->isCanDelete()) {
            $this->messageManager->addError(__('This store view cannot be deleted.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
            $redirectResult = $this->resultRedirectFactory->create();
            return $redirectResult->setPath('adminhtml/*/editStore', ['store_id' => $itemId]);
        }

        $this->_addDeletionNotice('store view');

        $resultPage = $this->createPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Delete Store View'));
        $resultPage->addBreadcrumb(__('Delete Store View'), __('Delete Store View'))
            ->addContent(
                $resultPage->getLayout()->createBlock(\Magento\Backend\Block\System\Store\Delete::class)
                    ->setFormActionUrl($this->getUrl('adminhtml/*/deleteStorePost'))
                    ->setBackUrl($this->getUrl('adminhtml/*/editStore', ['store_id' => $itemId]))
                    ->setStoreTypeTitle(__('Store View'))
                    ->setDataObject($model)
            );
        return $resultPage;
    }
}

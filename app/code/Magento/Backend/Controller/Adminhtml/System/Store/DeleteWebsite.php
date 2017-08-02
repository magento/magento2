<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;

/**
 * Class \Magento\Backend\Controller\Adminhtml\System\Store\DeleteWebsite
 *
 * @since 2.0.0
 */
class DeleteWebsite extends \Magento\Backend\Controller\Adminhtml\System\Store
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @since 2.0.0
     */
    public function execute()
    {
        $itemId = $this->getRequest()->getParam('item_id', null);
        if (!($model = $this->_objectManager->create(\Magento\Store\Model\Website::class)->load($itemId))) {
            $this->messageManager->addError(__('Something went wrong. Please try again.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
            $redirectResult = $this->resultRedirectFactory->create();
            return $redirectResult->setPath('adminhtml/*/');
        }
        if (!$model->isCanDelete()) {
            $this->messageManager->addError(__('This website cannot be deleted.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
            $redirectResult = $this->resultRedirectFactory->create();
            return $redirectResult->setPath('adminhtml/*/editWebsite', ['website_id' => $itemId]);
        }

        $this->_addDeletionNotice('website');

        $resultPage = $this->createPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Delete Web Site'));
        $resultPage->addBreadcrumb(__('Delete Web Site'), __('Delete Web Site'))
            ->addContent(
                $resultPage->getLayout()->createBlock(\Magento\Backend\Block\System\Store\Delete::class)
                    ->setFormActionUrl($this->getUrl('adminhtml/*/deleteWebsitePost'))
                    ->setBackUrl($this->getUrl('adminhtml/*/editWebsite', ['website_id' => $itemId]))
                    ->setStoreTypeTitle(__('Web Site'))->setDataObject($model)
            );
        return $resultPage;
    }
}

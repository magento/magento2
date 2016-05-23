<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;

class EditStore extends \Magento\Backend\Controller\Adminhtml\System\Store
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        if ($this->_getSession()->getPostData()) {
            $this->_coreRegistry->register('store_post_data', $this->_getSession()->getPostData());
            $this->_getSession()->unsPostData();
        }
        if (!$this->_coreRegistry->registry('store_type')) {
            $this->_coreRegistry->register('store_type', 'store');
        }
        if (!$this->_coreRegistry->registry('store_action')) {
            $this->_coreRegistry->register('store_action', 'edit');
        }
        switch ($this->_coreRegistry->registry('store_type')) {
            case 'website':
                $itemId = $this->getRequest()->getParam('website_id', null);
                $model = $this->_objectManager->create('Magento\Store\Model\Website');
                $title = __("Web Site");
                $notExists = __("The website does not exist.");
                $codeBase = __('Before modifying the website code please make sure it is not used in index.php.');
                break;
            case 'group':
                $itemId = $this->getRequest()->getParam('group_id', null);
                $model = $this->_objectManager->create('Magento\Store\Model\Group');
                $title = __("Store");
                $notExists = __("The store does not exist");
                $codeBase = false;
                break;
            case 'store':
                $itemId = $this->getRequest()->getParam('store_id', null);
                $model = $this->_objectManager->create('Magento\Store\Model\Store');
                $title = __("Store View");
                $notExists = __("Store view doesn't exist");
                $codeBase = __('Before modifying the store view code please make sure it is not used in index.php.');
                break;
            default:
                break;
        }
        if (null !== $itemId) {
            $model->load($itemId);
        }

        if ($model->getId() || $this->_coreRegistry->registry('store_action') == 'add') {
            $this->_coreRegistry->register('store_data', $model);
            if ($this->_coreRegistry->registry('store_action') == 'edit' && $codeBase && !$model->isReadOnly()) {
                $this->messageManager->addNotice($codeBase);
            }
            $resultPage = $this->createPage();
            if ($this->_coreRegistry->registry('store_action') == 'add') {
                $resultPage->getConfig()->getTitle()->prepend((__('New ') . $title));
            } else {
                $resultPage->getConfig()->getTitle()->prepend($model->getName());
            }
            $resultPage->getConfig()->getTitle()->prepend(__('Stores'));
            $resultPage->addContent($resultPage->getLayout()->createBlock('Magento\Backend\Block\System\Store\Edit'));
            return $resultPage;
        } else {
            $this->messageManager->addError($notExists);
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('adminhtml/*/');
        }
    }
}

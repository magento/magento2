<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types;

class NewAction extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types
{
    /**
     * Create new attribute set mapping
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $this->_initItemType();

            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->initPage()->addBreadcrumb(
                __('New attribute set mapping'),
                __('New attribute set mapping')
            );
            $resultPage->addContent(
                $resultPage->getLayout()->createBlock('Magento\GoogleShopping\Block\Adminhtml\Types\Edit')
            );
            $resultPage->getConfig()->getTitle()->prepend(__('Google Content Attributes'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Google Content Attribute Mapping'));
            return $resultPage;
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->messageManager->addError(__("We can't create Attribute Set Mapping."));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('adminhtml/*/index', ['store' => $this->_getStore()->getId()]);
            return $resultRedirect;
        }
    }
}

<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

class Delete extends \Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite
{
    /**
     * URL rewrite delete action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();
        if ($this->_getUrlRewrite()->getId()) {
            try {
                $this->_getUrlRewrite()->delete();
                $this->messageManager->addSuccess(__('The URL Rewrite has been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('An error occurred while deleting URL Rewrite.'));
                $redirect->setPath('adminhtml/*/edit/', ['id' => $this->_getUrlRewrite()->getId()]);
                return $redirect;
            }
        }
        $redirect->setPath('adminhtml/*/');
        return $redirect;
    }
}

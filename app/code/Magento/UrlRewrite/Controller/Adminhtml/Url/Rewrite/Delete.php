<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

class Delete extends \Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite
{
    /**
     * URL rewrite delete action
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_getUrlRewrite()->getId()) {
            try {
                $this->_getUrlRewrite()->delete();
                $this->messageManager->addSuccess(__('You deleted the URL rewrite.'));
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t delete URL Rewrite right now.'));
                $this->_redirect('adminhtml/*/edit/', ['id' => $this->_getUrlRewrite()->getId()]);
                return;
            }
        }
        $this->_redirect('adminhtml/*/');
    }
}

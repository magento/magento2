<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

use Magento\Framework\Exception\NotFoundException;

class Delete extends \Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite
{
    /**
     * URL rewrite delete action
     *
     * @return void
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }

        $id = (int)$this->_getUrlRewrite()->getId();
        if ($id) {
            try {
                $this->_getUrlRewrite()->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the URL rewrite.'));
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t delete URL Rewrite right now.'));
                $this->_redirect('adminhtml/*/edit/', ['id' => $this->_getUrlRewrite()->getId()]);
                return;
            }
        }
        $this->_redirect('adminhtml/*/');
    }
}

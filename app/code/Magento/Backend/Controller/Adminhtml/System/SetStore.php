<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System;

/**
 * Class \Magento\Backend\Controller\Adminhtml\System\SetStore
 *
 */
class SetStore extends \Magento\Backend\Controller\Adminhtml\System
{
    /**
     * @return void
     */
    public function execute()
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        if ($storeId) {
            $this->_session->setStoreId($storeId);
        }
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
    }
}

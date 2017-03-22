<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System;

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

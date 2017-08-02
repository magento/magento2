<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Auth;

/**
 * Class \Magento\Backend\Controller\Adminhtml\Auth\Logout
 *
 * @since 2.0.0
 */
class Logout extends \Magento\Backend\Controller\Adminhtml\Auth
{
    /**
     * Administrator logout action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_auth->logout();
        $this->messageManager->addSuccess(__('You have logged out.'));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath($this->_helper->getHomePageUrl());
    }
}

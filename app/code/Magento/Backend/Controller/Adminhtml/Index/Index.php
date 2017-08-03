<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Index;

/**
 * Class \Magento\Backend\Controller\Adminhtml\Index\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Backend\Controller\Adminhtml\Index
{
    /**
     * Admin area entry point
     * Always redirects to the startup page url
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath($this->_backendUrl->getStartupPageUrl());
    }
}

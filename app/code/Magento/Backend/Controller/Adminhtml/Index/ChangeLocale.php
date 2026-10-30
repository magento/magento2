<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Index;

class ChangeLocale extends \Magento\Backend\Controller\Adminhtml\Index
{
    /**
     * Change locale action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $redirectResult = $this->resultRedirectFactory->create();
        $redirectResult->setRefererUrl();
        return $redirectResult;
    }
}

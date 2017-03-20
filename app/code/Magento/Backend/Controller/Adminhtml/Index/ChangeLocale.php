<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

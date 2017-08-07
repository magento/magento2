<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Index;

/**
 * Class \Magento\Backend\Controller\Adminhtml\Index\ChangeLocale
 *
 */
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

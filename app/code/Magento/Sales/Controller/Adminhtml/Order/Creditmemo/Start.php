<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Creditmemo;

class Start extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_creditmemo';

    /**
     * Start create creditmemo action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /**
         * Clear old values for creditmemo qty's
         */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/*/new', ['_current' => true]);
        return $resultRedirect;
    }
}

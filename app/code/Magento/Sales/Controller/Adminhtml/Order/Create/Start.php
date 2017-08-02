<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use Magento\Backend\App\Action;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\Create\Start
 *
 * @since 2.0.0
 */
class Start extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Start order create action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_getSession()->clearStorage();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/*', ['customer_id' => $this->getRequest()->getParam('customer_id')]);
    }
}

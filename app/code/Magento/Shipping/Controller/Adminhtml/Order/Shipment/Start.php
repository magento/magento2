<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Start extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Sales::ship';

    /**
     * Start create shipment action
     *
     * @return Redirect
     */
    public function execute()
    {
        /**
         * Clear old values for shipment qty's
         */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
        return $resultRedirect;
    }
}

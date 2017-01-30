<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Creditmemo\AbstractCreditmemo;

/**
 * Class Email
 *
 * @package Magento\Sales\Controller\Adminhtml\Creditmemo\AbstractCreditmemo
 */
class Email extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_creditmemo';

    /**
     * Notify user
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        if (!$creditmemoId) {
            return;
        }
        $this->_objectManager->create('Magento\Sales\Api\CreditmemoManagementInterface')
            ->notify($creditmemoId);

        $this->messageManager->addSuccess(__('You sent the message.'));
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order_creditmemo/view', ['creditmemo_id' => $creditmemoId]);
        return $resultRedirect;
    }
}

<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Controller\Adminhtml\Creditmemo\AbstractCreditmemo;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action;

class Email extends Action implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Sales::sales_creditmemo';

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
        $creditmemo = $this->_objectManager->create(\Magento\Sales\Api\CreditmemoRepositoryInterface::class)
            ->get($creditmemoId);
        $isEnabled = (bool)$creditmemo->getStore()->getConfig('sales_email/creditmemo/enabled');
        if (!$isEnabled) {
            $this->messageManager->addWarningMessage(
                __('Credit memo emails are disabled for this store. No email was sent.')
            );
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('sales/order_creditmemo/view', ['creditmemo_id' => $creditmemoId]);
            return $resultRedirect;
        }
        $this->_objectManager->create(\Magento\Sales\Api\CreditmemoManagementInterface::class)
            ->notify($creditmemoId);

        $this->messageManager->addSuccessMessage(__('You sent the message.'));
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order_creditmemo/view', ['creditmemo_id' => $creditmemoId]);
        return $resultRedirect;
    }
}

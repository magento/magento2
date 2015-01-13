<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
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
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_creditmemo');
    }

    /**
     * Notify user
     *
     * @return void
     */
    public function execute()
    {
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        if (!$creditmemoId) {
            return;
        }
        $creditmemo = $this->_objectManager->create('Magento\Sales\Model\Order\Creditmemo')->load($creditmemoId);
        if (!$creditmemo) {
            return;
        }
        $this->_objectManager->create('Magento\Sales\Model\Order\CreditmemoNotifier')
            ->notify($creditmemo);

        $this->messageManager->addSuccess(__('We sent the message.'));
        $this->_redirect('sales/order_creditmemo/view', ['creditmemo_id' => $creditmemoId]);
    }
}

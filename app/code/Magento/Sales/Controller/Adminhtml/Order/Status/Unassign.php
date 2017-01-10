<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Status;

class Unassign extends \Magento\Sales\Controller\Adminhtml\Order\Status
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $state = $this->getRequest()->getParam('state');
        $status = $this->_initStatus();
        if ($status) {
            try {
                $status->unassignState($state);
                $this->messageManager->addSuccess(__('You have unassigned the order status.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while unassigning the order.')
                );
            }
        } else {
            $this->messageManager->addError(__('We can\'t find this order status.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/*/');
    }
}

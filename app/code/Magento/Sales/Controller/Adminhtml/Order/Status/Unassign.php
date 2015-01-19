<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Status;

class Unassign extends \Magento\Sales\Controller\Adminhtml\Order\Status
{
    /**
     * @return void
     */
    public function execute()
    {
        $state = $this->getRequest()->getParam('state');
        $status = $this->_initStatus();
        if ($status) {
            try {
                $status->unassignState($state);
                $this->messageManager->addSuccess(__('You have unassigned the order status.'));
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while we were unassigning the order.')
                );
            }
        } else {
            $this->messageManager->addError(__('We can\'t find this order status.'));
        }
        $this->_redirect('sales/*/');
    }
}

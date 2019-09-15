<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Status;

use Magento\Framework\App\Action\HttpPostActionInterface;

class Unassign extends \Magento\Sales\Controller\Adminhtml\Order\Status implements HttpPostActionInterface
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
                $this->messageManager->addSuccessMessage(__('You have unassigned the order status.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while unassigning the order.')
                );
            }
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find this order status.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/*/');
    }
}

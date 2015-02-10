<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Status;

use Magento\Framework\Registry;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;

class Unassign extends \Magento\Sales\Controller\Adminhtml\Order\Status
{
    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context, $coreRegistry);
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

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
                    __('Something went wrong while we were unassigning the order.')
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

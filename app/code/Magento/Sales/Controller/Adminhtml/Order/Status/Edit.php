<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Status;

use Magento\Framework\Registry;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magento\Sales\Controller\Adminhtml\Order\Status
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $coreRegistry);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Editing existing status form
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $status = $this->_initStatus();
        if ($status) {
            $this->_coreRegistry->register('current_status', $status);
            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Magento_Sales::system_order_statuses');
            $resultPage->getConfig()->getTitle()->prepend(__('Order Status'));
            $resultPage->getConfig()->getTitle()->prepend(__('Edit Order Status'));
            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find this order status.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('sales/');
        }
    }
}

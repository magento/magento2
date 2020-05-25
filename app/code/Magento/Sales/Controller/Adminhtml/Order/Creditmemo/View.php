<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Creditmemo;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;

class View extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_creditmemo';

    /**
     * @var CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param Action\Context $context
     * @param CreditmemoLoader $creditmemoLoader
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Action\Context $context,
        CreditmemoLoader $creditmemoLoader,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory
    ) {
        $this->creditmemoLoader = $creditmemoLoader;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * Creditmemo information page
     *
     * @return Page|Forward
     */
    public function execute()
    {
        $this->creditmemoLoader->setOrderId($this->getRequest()->getParam('order_id'));
        $this->creditmemoLoader->setCreditmemoId($this->getRequest()->getParam('creditmemo_id'));
        $this->creditmemoLoader->setCreditmemo($this->getRequest()->getParam('creditmemo'));
        $this->creditmemoLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));

        try {
            $creditmemo = $this->creditmemoLoader->load();
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This creditmemo no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('sales/creditmemo');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('sales_creditmemo_view')
                ->updateBackButtonUrl($this->getRequest()->getParam('come_from'));
        $resultPage->setActiveMenu('Magento_Sales::sales_creditmemo');
        if ($creditmemo->getInvoice()) {
            $resultPage->getConfig()->getTitle()->prepend(
                __("View Memo for #%1", $creditmemo->getInvoice()->getIncrementId())
            );
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__("View Memo"));
        }
        return $resultPage;
    }
}

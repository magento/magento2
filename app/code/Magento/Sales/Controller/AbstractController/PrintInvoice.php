<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Controller\AbstractController;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

abstract class PrintInvoice extends \Magento\Framework\App\Action\Action
{
    /**
     * @var OrderViewAuthorizationInterface
     */
    protected $orderAuthorization;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var OrderRepositoryInterface
     */
    protected $order;

    /**
     * @param Context $context
     * @param OrderViewAuthorizationInterface $orderAuthorization
     * @param Registry $registry
     * @param PageFactory $resultPageFactory
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param Session $session
     * @param OrderRepositoryInterface $order
     */
    public function __construct(
        Context $context,
        OrderViewAuthorizationInterface $orderAuthorization,
        Registry $registry,
        PageFactory $resultPageFactory,
        InvoiceRepositoryInterface $invoiceRepository,
        Session $session,
        OrderRepositoryInterface $order
    ) {
        $this->orderAuthorization = $orderAuthorization;
        $this->_coreRegistry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->invoiceRepository = $invoiceRepository;
        $this->session = $session;
        $this->order = $order;
        parent::__construct($context);
    }

    /**
     * Print Invoice Action
     *
     * @return Redirect|Page
     */
    public function execute()
    {
        $invoiceId = (int)$this->getRequest()->getParam('invoice_id');
        if ($invoiceId) {
            try {
                $invoice = $this->invoiceRepository->get($invoiceId);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                if ($this->session->isLoggedIn()) {
                    $resultRedirect->setPath('*/*/history');
                } else {
                    $resultRedirect->setPath('sales/guest/form');
                }
                return $resultRedirect;
            }
            $order = $invoice->getOrder();
        } else {
            $orderId = (int)$this->getRequest()->getParam('order_id');
            $order = $this->order->load($orderId);
        }

        if ($this->orderAuthorization->canView($order)) {
            $this->_coreRegistry->register('current_order', $order);
            if (isset($invoice)) {
                $this->_coreRegistry->register('current_invoice', $invoice);
            }
            /** @var Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->addHandle('print');
            return $resultPage;
        } else {
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            if ($this->session->isLoggedIn()) {
                $resultRedirect->setPath('*/*/history');
            } else {
                $resultRedirect->setPath('sales/guest/form');
            }
            return $resultRedirect;
        }
    }
}

<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Service\InvoiceService;

class NewAction extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_invoice';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @param Action\Context $context
     * @param Registry $registry
     * @param PageFactory $resultPageFactory
     * @param InvoiceService $invoiceService
     */
    public function __construct(
        Action\Context $context,
        Registry $registry,
        PageFactory $resultPageFactory,
        InvoiceService $invoiceService
    ) {
        $this->registry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
        $this->invoiceService = $invoiceService;
    }

    /**
     * Redirect to order view page
     *
     * @param int $orderId
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function _redirectToOrder($orderId)
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        return $resultRedirect;
    }

    /**
     * Invoice create page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $invoiceData = $this->getRequest()->getParam('invoice', []);
        $invoiceItems = isset($invoiceData['items']) ? $invoiceData['items'] : [];

        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class)->load($orderId);
            if (!$order->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('The order no longer exists.'));
            }

            if (!$order->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The order does not allow an invoice to be created.')
                );
            }
            $invoice = $this->invoiceService->prepareInvoice($order, $invoiceItems);

            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("The invoice can't be created without products. Add products and try again.")
                );
            }
            $this->registry->register('current_invoice', $invoice);

            $comment = $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getCommentText(true);
            if ($comment) {
                $invoice->setCommentText($comment);
            }

            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Magento_Sales::sales_order');
            $resultPage->getConfig()->getTitle()->prepend(__('Invoices'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Invoice'));
            return $resultPage;
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            return $this->_redirectToOrder($orderId);
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, 'Cannot create an invoice.');
            return $this->_redirectToOrder($orderId);
        }
    }
}

<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Service\InvoiceService;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\Invoice\NewAction
 *
 * @since 2.0.0
 */
class NewAction extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_invoice';

    /**
     * @var Registry
     * @since 2.0.0
     */
    protected $registry;

    /**
     * @var PageFactory
     * @since 2.0.0
     */
    protected $resultPageFactory;

    /**
     * @var InvoiceService
     * @since 2.0.0
     */
    private $invoiceService;

    /**
     * @param Action\Context $context
     * @param Registry $registry
     * @param PageFactory $resultPageFactory
     * @param InvoiceService $invoiceService
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
                    __('You can\'t create an invoice without products.')
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
            $this->messageManager->addError($exception->getMessage());
            return $this->_redirectToOrder($orderId);
        } catch (\Exception $exception) {
            $this->messageManager->addException($exception, 'Cannot create an invoice.');
            return $this->_redirectToOrder($orderId);
        }
    }
}

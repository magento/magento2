<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Creditmemo;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_creditmemo';

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var CreditmemoSender
     */
    protected $creditmemoSender;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Sales\Model\Order\PaymentAdapterInterface
     */
    private $paymentAdapter;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader
     * @param CreditmemoSender $creditmemoSender
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        CreditmemoSender $creditmemoSender,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->creditmemoLoader = $creditmemoLoader;
        $this->creditmemoSender = $creditmemoSender;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * Save creditmemo
     * We can save only new creditmemo. Existing creditmemos are not editable
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Backend\Model\View\Result\Forward
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPost('creditmemo');
        if (!empty($data['comment_text'])) {
            $this->_getSession()->setCommentText($data['comment_text']);
        }

        $connection = $this->getResource()->getConnection('sales');
        $connection->beginTransaction();
        try {
            $this->creditmemoLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->creditmemoLoader->setCreditmemoId($this->getRequest()->getParam('creditmemo_id'));
            $this->creditmemoLoader->setCreditmemo($this->getRequest()->getParam('creditmemo'));
            $this->creditmemoLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));
            $creditmemo = $this->creditmemoLoader->load();
            if ($creditmemo) {
                if (!$creditmemo->isValidGrandTotal()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The credit memo\'s total must be positive.')
                    );
                }

                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );

                    $creditmemo->setCustomerNote($data['comment_text']);
                    $creditmemo->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                }

                if (isset($data['do_offline'])) {
                    //do not allow online refund for Refund to Store Credit
                    if (!$data['do_offline'] && !empty($data['refund_customerbalance_return_enable'])) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Cannot create online refund for Refund to Store Credit.')
                        );
                    }
                }
                $creditmemoManagement = $this->_objectManager->create(
                    'Magento\Sales\Api\CreditmemoManagementInterface'
                );
                $creditmemoManagement->refund($creditmemo, (bool)$data['do_offline']);

                $order = $this->getPaymentAdapter()->refund(
                    $creditmemo,
                    $creditmemo->getOrder(),
                    !(bool)$data['do_offline']
                );
                $this->getOrderRepository()->save($order);
                $invoice = $creditmemo->getInvoice();
                if ($invoice) {
                    $invoice->setIsUsedForRefund(true);
                    $invoice->setBaseTotalRefunded(
                        $invoice->getBaseTotalRefunded() + $creditmemo->getBaseGrandTotal()
                    );
                    $creditmemo->setInvoiceId($invoice->getId());
                    $this->getInvoiceRepository()->save($creditmemo->getInvoice());
                }
                $connection->commit();

                if (!empty($data['send_email'])) {
                    $this->creditmemoSender->send($creditmemo);
                }

                $this->messageManager->addSuccess(__('You created the credit memo.'));
                $this->_getSession()->getCommentText(true);
                $resultRedirect->setPath('sales/order/view', ['order_id' => $creditmemo->getOrderId()]);
                return $resultRedirect;
            } else {
                $resultForward = $this->resultForwardFactory->create();
                $resultForward->forward('noroute');
                return $resultForward;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_getSession()->setFormData($data);
            $connection->rollBack();
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->messageManager->addError(__('We can\'t save the credit memo right now.'));
            $connection->rollBack();
        }
        $resultRedirect->setPath('sales/*/new', ['_current' => true]);
        return $resultRedirect;
    }

    /**
     * @return \Magento\Sales\Model\Order\PaymentAdapterInterface
     *
     * @deprecated
     */
    private function getPaymentAdapter()
    {
        if ($this->paymentAdapter === null) {
            $this->paymentAdapter = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Sales\Model\Order\PaymentAdapterInterface::class);
        }
        return $this->paymentAdapter;
    }

    /**
     * @return \Magento\Framework\App\ResourceConnection|mixed
     *
     * @deprecated
     */
    private function getResource()
    {
        if ($this->resource === null) {
            $this->resource = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\App\ResourceConnection::class);
        }
        return $this->resource;
    }

    /**
     * @return \Magento\Sales\Api\OrderRepositoryInterface
     *
     * @deprecated
     */
    private function getOrderRepository()
    {
        if ($this->orderRepository === null) {
            $this->orderRepository = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
        }
        return $this->orderRepository;
    }

    /**
     * @return \Magento\Sales\Api\InvoiceRepositoryInterface
     *
     * @deprecated
     */
    private function getInvoiceRepository()
    {
        if ($this->invoiceRepository === null) {
            $this->invoiceRepository = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
        }
        return $this->invoiceRepository;
    }
}

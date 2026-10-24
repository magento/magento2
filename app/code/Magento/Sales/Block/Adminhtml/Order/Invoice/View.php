<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

namespace Magento\Sales\Block\Adminhtml\Order\Invoice;

/**
 * Adminhtml invoice create
 *
 * @api
 * @since 100.0.2
 */
class View extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Admin session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_session;

    /**
     * Application data storage
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Admin user authentication service
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendSession;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Backend\Model\Auth\Session $backendSession
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Model\Auth\Session $backendSession,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_backendSession = $backendSession;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Constructor
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _construct()
    {
        $this->initializeConfiguration();
        parent::_construct();
        $this->removeDefaultButtons();

        if (!$this->getInvoice()) {
            return;
        }

        $this->addInvoiceButtons();
    }

    /**
     * Initialize basic configuration
     *
     * @return void
     */
    private function initializeConfiguration()
    {
        $this->_objectId = 'invoice_id';
        $this->_controller = 'adminhtml_order_invoice';
        $this->_mode = 'view';
        $this->_session = $this->_backendSession;
    }

    /**
     * Remove default buttons
     *
     * @return void
     */
    private function removeDefaultButtons()
    {
        $this->buttonList->remove('save');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
    }

    /**
     * Add all invoice-specific buttons
     *
     * @return void
     */
    private function addInvoiceButtons()
    {
        $this->addCancelButton();
        $this->addSendEmailButton();
        $this->addCreditMemoButton();
        $this->addCaptureButton();
        $this->addVoidButton();
        $this->addPrintButton();
    }

    /**
     * Add cancel button if allowed and applicable
     *
     * @return void
     */
    private function addCancelButton()
    {
        if ($this->_isAllowedAction(
            'Magento_Sales::cancel'
        ) && $this->getInvoice()->canCancel() && !$this->_isPaymentReview()
        ) {
            $this->buttonList->add(
                'cancel',
                [
                    'label' => __('Cancel'),
                    'class' => 'delete',
                    'onclick' => 'setLocation(\'' . $this->getCancelUrl() . '\')'
                ]
            );
        }
    }

    /**
     * Add send email button if allowed
     *
     * @return void
     */
    private function addSendEmailButton()
    {
        if ($this->_isAllowedAction('Magento_Sales::emails')) {
            $confirmMessage = $this->escapeJs(
                $this->escapeHtml(__('Are you sure you want to send an invoice email to customer?'))
            );
            $this->addButton(
                'send_notification',
                [
                    'label' => __('Send Email'),
                    'class' => 'send-email',
                    'onclick' => 'confirmSetLocation(\'' . $confirmMessage . '\', \'' . $this->getEmailUrl() . '\')'
                ]
            );
        }
    }

    /**
     * Add credit memo button if allowed and applicable
     *
     * @return void
     */
    private function addCreditMemoButton()
    {
        $orderPayment = $this->getInvoice()->getOrder()->getPayment();

        if ($this->_isAllowedAction('Magento_Sales::creditmemo') && $this->getInvoice()->getOrder()->canCreditmemo()) {
            if ($orderPayment->canRefundPartialPerInvoice() &&
                $this->getInvoice()->canRefund() &&
                $orderPayment->getAmountPaid() > $orderPayment->getAmountRefunded() ||
                $orderPayment->canRefund() && !$this->getInvoice()->getIsUsedForRefund()
            ) {
                $this->buttonList->add(
                    'credit-memo',
                    [
                        'label' => __('Credit Memo'),
                        'class' => 'credit-memo',
                        'onclick' => 'setLocation(\'' . $this->getCreditMemoUrl() . '\')'
                    ]
                );
            }
        }
    }

    /**
     * Add capture button if allowed and applicable
     *
     * @return void
     */
    private function addCaptureButton()
    {
        if ($this->_isAllowedAction(
            'Magento_Sales::capture'
        ) && $this->getInvoice()->canCapture() && !$this->_isPaymentReview()
        ) {
            $this->buttonList->add(
                'capture',
                [
                    'label' => __('Capture'),
                    'class' => 'capture',
                    'onclick' => 'setLocation(\'' . $this->getCaptureUrl() . '\')'
                ]
            );
        }
    }

    /**
     * Add void button if applicable
     *
     * @return void
     */
    private function addVoidButton()
    {
        if ($this->getInvoice()->canVoid()) {
            $this->buttonList->add(
                'void',
                [
                    'label' => __('Void'),
                    'class' => 'void',
                    'onclick' => 'setLocation(\'' . $this->getVoidUrl() . '\')'
                ]
            );
        }
    }

    /**
     * Add print button if invoice has ID
     *
     * @return void
     */
    private function addPrintButton()
    {
        if ($this->getInvoice()->getId()) {
            $this->buttonList->add(
                'print',
                [
                    'label' => __('Print'),
                    'class' => 'print',
                    'onclick' => 'setLocation(\'' . $this->getPrintUrl() . '\')'
                ]
            );
        }
    }

    /**
     * Check whether order is under payment review
     *
     * @return bool
     */
    protected function _isPaymentReview()
    {
        $order = $this->getInvoice()->getOrder();
        return $order->canReviewPayment() || $order->canFetchPaymentReviewUpdate();
    }

    /**
     * Retrieve invoice model instance
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getInvoice()
    {
        return $this->_coreRegistry->registry('current_invoice');
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->getInvoice()->getEmailSent()) {
            $emailSent = __('The invoice email was sent.');
        } else {
            $emailSent = __('The invoice email wasn\'t sent.');
        }
        return __(
            'Invoice #%1 | %2 | %4 (%3)',
            $this->getInvoice()->getIncrementId(),
            $this->getInvoice()->getStateName(),
            $emailSent,
            $this->formatDate(
                $this->_localeDate->date(new \DateTime($this->getInvoice()->getCreatedAt())),
                \IntlDateFormatter::MEDIUM,
                true
            )
        );
    }

    /**
     * Get back url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl(
            'sales/order/view',
            [
                'order_id' => $this->getInvoice() ? $this->getInvoice()->getOrderId() : null,
                'active_tab' => 'order_invoices'
            ]
        );
    }

    /**
     * Get capture url
     *
     * @return string
     */
    public function getCaptureUrl()
    {
        return $this->getUrl('sales/*/capture', ['invoice_id' => $this->getInvoice()->getId()]);
    }

    /**
     * Get void url
     *
     * @return string
     */
    public function getVoidUrl()
    {
        return $this->getUrl('sales/*/void', ['invoice_id' => $this->getInvoice()->getId()]);
    }

    /**
     * Get cancel url
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->getUrl('sales/*/cancel', ['invoice_id' => $this->getInvoice()->getId()]);
    }

    /**
     * Get email url
     *
     * @return string
     */
    public function getEmailUrl()
    {
        return $this->getUrl(
            'sales/*/email',
            ['order_id' => $this->getInvoice()->getOrder()->getId(), 'invoice_id' => $this->getInvoice()->getId()]
        );
    }

    /**
     * Get credit memo url
     *
     * @return string
     */
    public function getCreditMemoUrl()
    {
        return $this->getUrl(
            'sales/order_creditmemo/start',
            ['order_id' => $this->getInvoice()->getOrder()->getId(), 'invoice_id' => $this->getInvoice()->getId()]
        );
    }

    /**
     * Get print url
     *
     * @return string
     */
    public function getPrintUrl()
    {
        return $this->getUrl('sales/*/print', ['invoice_id' => $this->getInvoice()->getId()]);
    }

    /**
     * Update back button url
     *
     * @param bool $flag
     * @return $this
     */
    public function updateBackButtonUrl($flag)
    {
        if ($flag) {
            if ($this->getInvoice()->getBackUrl()) {
                return $this->buttonList->update(
                    'back',
                    'onclick',
                    'setLocation(\'' . $this->getInvoice()->getBackUrl() . '\')'
                );
            }
            return $this->buttonList->update(
                'back',
                'onclick',
                'setLocation(\'' . $this->getUrl('sales/invoice/') . '\')'
            );
        }
        return $this;
    }

    /**
     * Check whether is allowed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}

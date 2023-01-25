<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order;

use Magento\Sales\Model\ConfigInterface;

/**
 * Adminhtml sales order view
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class View extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Block group
     *
     * @var string
     */
    protected $_blockGroup = 'Magento_Sales';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Sales config
     *
     * @var \Magento\Sales\Model\Config
     */
    protected $_salesConfig;

    /**
     * Reorder helper
     *
     * @var \Magento\Sales\Helper\Reorder
     */
    protected $_reorderHelper;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ConfigInterface $salesConfig
     * @param \Magento\Sales\Helper\Reorder $reorderHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        ConfigInterface $salesConfig,
        \Magento\Sales\Helper\Reorder $reorderHelper,
        array $data = []
    ) {
        $this->_reorderHelper = $reorderHelper;
        $this->_coreRegistry = $registry;
        $this->_salesConfig = $salesConfig;
        parent::__construct($context, $data);
    }

    /**
     * Constructor
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _construct()
    {
        $this->_objectId = 'order_id';
        $this->_controller = 'adminhtml_order';
        $this->_mode = 'view';

        parent::_construct();

        $this->removeButton('delete');
        $this->removeButton('reset');
        $this->removeButton('save');
        $this->setId('sales_order_view');
        $order = $this->getOrder();

        if (!$order) {
            return;
        }

        if ($this->_isAllowedAction('Magento_Sales::actions_edit') && $order->canEdit()) {
            $onclickJs = 'jQuery(\'#order_edit\').orderEditDialog({message: \''
                . $this->getEditMessage($order) . '\', url: \'' . $this->getEditUrl()
                . '\'}).orderEditDialog(\'showDialog\');';

            $this->addButton(
                'order_edit',
                [
                    'label' => __('Edit'),
                    'class' => 'edit primary',
                    'onclick' => $onclickJs,
                    'data_attribute' => [
                        'mage-init' => '{"orderEditDialog":{}}',
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::cancel') && $order->canCancel()) {
            $this->addButton(
                'order_cancel',
                [
                    'label' => __('Cancel'),
                    'class' => 'cancel',
                    'id' => 'order-view-cancel-button',
                    'data_attribute' => [
                        'url' => $this->getCancelUrl()
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::emails') && !$order->isCanceled()) {
            $message = __('Are you sure you want to send an order email to customer?');
            $this->addButton(
                'send_notification',
                [
                    'label' => __('Send Email'),
                    'class' => 'send-email',
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getEmailUrl()}')"
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::creditmemo') && $order->canCreditmemo()) {
            $message = __(
                'This will create an offline refund. ' .
                'To create an online refund, open an invoice and create credit memo for it. Do you want to continue?'
            );
            $onClick = "setLocation('{$this->getCreditmemoUrl()}')";
            if ($order->getPayment()->getMethodInstance()->isGateway()) {
                $onClick = "confirmSetLocation('{$message}', '{$this->getCreditmemoUrl()}')";
            }
            $this->addButton(
                'order_creditmemo',
                ['label' => __('Credit Memo'), 'onclick' => $onClick, 'class' => 'credit-memo']
            );
        }

        // invoice action intentionally
        if ($this->_isAllowedAction('Magento_Sales::invoice') && $order->canVoidPayment()) {
            $message = __('Are you sure you want to void the payment?');
            $this->addButton(
                'void_payment',
                [
                    'label' => __('Void'),
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getVoidPaymentUrl()}')"
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::hold') && $order->canHold()) {
            $this->addButton(
                'order_hold',
                [
                    'label' => __('Hold'),
                    'class' => __('hold'),
                    'id' => 'order-view-hold-button',
                    'data_attribute' => [
                        'url' => $this->getHoldUrl()
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::unhold') && $order->canUnhold()) {
            $this->addButton(
                'order_unhold',
                [
                    'label' => __('Unhold'),
                    'class' => __('unhold'),
                    'id' => 'order-view-unhold-button',
                    'data_attribute' => [
                        'url' => $this->getUnholdUrl()
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::review_payment')) {
            if ($order->canReviewPayment()) {
                $message = __('Are you sure you want to accept this payment?');
                $this->addButton(
                    'accept_payment',
                    [
                        'label' => __('Accept Payment'),
                        'onclick' => "confirmSetLocation('{$message}', '{$this->getReviewPaymentUrl('accept')}')"
                    ]
                );
                $message = __('Are you sure you want to deny this payment?');
                $this->addButton(
                    'deny_payment',
                    [
                        'label' => __('Deny Payment'),
                        'onclick' => "confirmSetLocation('{$message}', '{$this->getReviewPaymentUrl('deny')}')"
                    ]
                );
            }
            if ($order->canFetchPaymentReviewUpdate()) {
                $this->addButton(
                    'get_review_payment_update',
                    [
                        'label' => __('Get Payment Update'),
                        'onclick' => 'setLocation(\'' . $this->getReviewPaymentUrl('update') . '\')'
                    ]
                );
            }
        }

        if ($this->_isAllowedAction('Magento_Sales::invoice') && $order->canInvoice()) {
            $_label = $order->getForcedShipmentWithInvoice() ? __('Invoice and Ship') : __('Invoice');
            $this->addButton(
                'order_invoice',
                [
                    'label' => $_label,
                    'onclick' => 'setLocation(\'' . $this->getInvoiceUrl() . '\')',
                    'class' => 'invoice'
                ]
            );
        }

        if ($this->_isAllowedAction(
            'Magento_Sales::ship'
        ) && $order->canShip() && !$order->getForcedShipmentWithInvoice()
        ) {
            $this->addButton(
                'order_ship',
                [
                    'label' => __('Ship'),
                    'onclick' => 'setLocation(\'' . $this->getShipUrl() . '\')',
                    'class' => 'ship'
                ]
            );
        }

        if ($this->_isAllowedAction(
            'Magento_Sales::reorder'
        ) && $this->_reorderHelper->isAllowed(
            $order->getStore()
        ) && $order->canReorderIgnoreSalable()
        ) {
            $this->addButton(
                'order_reorder',
                [
                    'label' => __('Reorder'),
                    'onclick' => 'setLocation(\'' . $this->getReorderUrl() . '\')',
                    'class' => 'reorder'
                ]
            );
        }
    }

    /**
     * Retrieve order model object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('sales_order');
    }

    /**
     * Retrieve Order Identifier
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getOrder() ? $this->getOrder()->getId() : null;
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $_extOrderId = $this->getOrder()->getExtOrderId();
        if ($_extOrderId) {
            $_extOrderId = '[' . $_extOrderId . '] ';
        } else {
            $_extOrderId = '';
        }
        return __(
            'Order # %1 %2 | %3',
            $this->getOrder()->getRealOrderId(),
            $_extOrderId,
            $this->formatDate(
                $this->_localeDate->date(new \DateTime($this->getOrder()->getCreatedAt())),
                \IntlDateFormatter::MEDIUM,
                true
            )
        );
    }

    /**
     * URL getter
     *
     * @param string $params
     * @param array $params2
     * @return string
     */
    public function getUrl($params = '', $params2 = [])
    {
        $params2['order_id'] = $this->getOrderId();
        return parent::getUrl($params, $params2);
    }

    /**
     * Edit URL getter
     *
     * @return string
     */
    public function getEditUrl()
    {
        return $this->getUrl('sales/order_edit/start');
    }

    /**
     * Email URL getter
     *
     * @return string
     */
    public function getEmailUrl()
    {
        return $this->getUrl('sales/*/email');
    }

    /**
     * Cancel URL getter
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->getUrl('sales/*/cancel');
    }

    /**
     * Invoice URL getter
     *
     * @return string
     */
    public function getInvoiceUrl()
    {
        return $this->getUrl('sales/order_invoice/start');
    }

    /**
     * Credit memo URL getter
     *
     * @return string
     */
    public function getCreditmemoUrl()
    {
        return $this->getUrl('sales/order_creditmemo/start');
    }

    /**
     * Hold URL getter
     *
     * @return string
     */
    public function getHoldUrl()
    {
        return $this->getUrl('sales/*/hold');
    }

    /**
     * Unhold URL getter
     *
     * @return string
     */
    public function getUnholdUrl()
    {
        return $this->getUrl('sales/*/unhold');
    }

    /**
     * Ship URL getter
     *
     * @return string
     */
    public function getShipUrl()
    {
        return $this->getUrl('adminhtml/order_shipment/start');
    }

    /**
     * Comment URL getter
     *
     * @return string
     */
    public function getCommentUrl()
    {
        return $this->getUrl('sales/*/comment');
    }

    /**
     * Reorder URL getter
     *
     * @return string
     */
    public function getReorderUrl()
    {
        return $this->getUrl('sales/order_create/reorder');
    }

    /**
     * Payment void URL getter
     *
     * @return string
     */
    public function getVoidPaymentUrl()
    {
        return $this->getUrl('sales/*/voidPayment');
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Return back url for view grid
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getRequest()->getParam('customer_id')) {
            return $this->getUrl('customer/index/edit', ['id'=> $this->getRequest()->getParam('customer_id')]);
        }
        if ($this->getOrder() && $this->getOrder()->getBackUrl()) {
            return $this->getOrder()->getBackUrl();
        }

        return $this->getUrl('sales/*/');
    }

    /**
     * Payment review URL getter
     *
     * @param string $action
     * @return string
     */
    public function getReviewPaymentUrl($action)
    {
        return $this->getUrl('sales/*/reviewPayment', ['action' => $action]);
    }

    /**
     * Get edit message
     *
     * @param \Magento\Sales\Model\Order $order
     * @return \Magento\Framework\Phrase
     */
    protected function getEditMessage($order)
    {
        // see if order has non-editable products as items
        $nonEditableTypes = $this->getNonEditableTypes($order);
        if (!empty($nonEditableTypes)) {
            return __(
                'This order contains (%1) items and therefore cannot be edited through the admin interface. ' .
                'If you wish to continue editing, the (%2) items will be removed, ' .
                ' the order will be canceled and a new order will be placed.',
                implode(', ', $nonEditableTypes),
                implode(', ', $nonEditableTypes)
            );
        }
        return __('Are you sure? This order will be canceled and a new one will be created instead.');
    }

    /**
     * Get non editable types
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    protected function getNonEditableTypes($order)
    {
        return array_keys(
            $this->getOrder()->getResource()->aggregateProductsByTypes(
                $order->getId(),
                $this->_salesConfig->getAvailableProductTypes(),
                false
            )
        );
    }
}

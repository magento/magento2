<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Invoice\Create;

/**
 * Adminhtml invoice items grid
 *
 * @api
 * @since 2.0.0
 */
class Items extends \Magento\Sales\Block\Adminhtml\Items\AbstractItems
{
    /**
     * Disable submit button
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_disableSubmitButton = false;

    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     * @since 2.0.0
     */
    protected $_salesData;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Data $salesData
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Data $salesData,
        array $data = []
    ) {
        $this->_salesData = $salesData;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $data);
    }

    /**
     * Prepare child blocks
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        $onclick = "submitAndReloadArea($('invoice_item_container'),'" . $this->getUpdateUrl() . "')";
        $this->addChild(
            'update_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['class' => 'update-button', 'label' => __('Update Qty\'s'), 'onclick' => $onclick]
        );
        $this->_disableSubmitButton = true;
        $submitButtonClass = ' disabled';
        foreach ($this->getInvoice()->getAllItems() as $item) {
            /**
             * @see bug #14839
             */
            if ($item->getQty()/* || $this->getSource()->getData('base_grand_total')*/) {
                $this->_disableSubmitButton = false;
                $submitButtonClass = '';
                break;
            }
        }
        if ($this->getOrder()->getForcedShipmentWithInvoice()) {
            $_submitLabel = __('Submit Invoice and Shipment');
        } else {
            $_submitLabel = __('Submit Invoice');
        }
        $this->addChild(
            'submit_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => $_submitLabel,
                'class' => 'save submit-button primary' . $submitButtonClass,
                'onclick' => 'disableElements(\'submit-button\');$(\'edit_form\').submit()',
                'disabled' => $this->_disableSubmitButton
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Get is submit button disabled or not
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getDisableSubmitButton()
    {
        return $this->_disableSubmitButton;
    }

    /**
     * Retrieve invoice order
     *
     * @return \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    public function getOrder()
    {
        return $this->getInvoice()->getOrder();
    }

    /**
     * Retrieve source
     *
     * @return \Magento\Sales\Model\Order\Invoice
     * @since 2.0.0
     */
    public function getSource()
    {
        return $this->getInvoice();
    }

    /**
     * Retrieve invoice model instance
     *
     * @return \Magento\Sales\Model\Order\Invoice
     * @since 2.0.0
     */
    public function getInvoice()
    {
        return $this->_coreRegistry->registry('current_invoice');
    }

    /**
     * Retrieve order totals block settings
     *
     * @return array
     * @since 2.0.0
     */
    public function getOrderTotalData()
    {
        return [];
    }

    /**
     * Retrieve order totalbar block data
     *
     * @return array
     * @since 2.0.0
     */
    public function getOrderTotalbarData()
    {
        $this->setPriceDataObject($this->getInvoice()->getOrder());

        $totalbarData = [];
        $totalbarData[] = [__('Paid Amount'), $this->displayPriceAttribute('amount_paid'), false];
        $totalbarData[] = [__('Refund Amount'), $this->displayPriceAttribute('amount_refunded'), false];
        $totalbarData[] = [__('Shipping Amount'), $this->displayPriceAttribute('shipping_captured'), false];
        $totalbarData[] = [__('Shipping Refund'), $this->displayPriceAttribute('shipping_refunded'), false];
        $totalbarData[] = [__('Order Grand Total'), $this->displayPriceAttribute('grand_total'), true];
        return $totalbarData;
    }

    /**
     * Format price
     *
     * @param float $price
     * @return string
     * @since 2.0.0
     */
    public function formatPrice($price)
    {
        return $this->getInvoice()->getOrder()->formatPrice($price);
    }

    /**
     * Get update button html
     *
     * @return string
     * @since 2.0.0
     */
    public function getUpdateButtonHtml()
    {
        return $this->getChildHtml('update_button');
    }

    /**
     * Get update url
     *
     * @return string
     * @since 2.0.0
     */
    public function getUpdateUrl()
    {
        return $this->getUrl('sales/*/updateQty', ['order_id' => $this->getInvoice()->getOrderId()]);
    }

    /**
     * Check shipment availability for current invoice
     *
     * @return bool
     * @since 2.0.0
     */
    public function canCreateShipment()
    {
        foreach ($this->getInvoice()->getAllItems() as $item) {
            if ($item->getOrderItem()->getQtyToShip()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if qty can be edited
     *
     * @return bool
     * @since 2.0.0
     */
    public function canEditQty()
    {
        if ($this->getInvoice()->getOrder()->getPayment()->canCapture()) {
            return $this->getInvoice()->getOrder()->getPayment()->canCapturePartial();
        }
        return true;
    }

    /**
     * Check if capture operation is allowed in ACL
     *
     * @return bool
     * @since 2.0.0
     */
    public function isCaptureAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::capture');
    }

    /**
     * Check if invoice can be captured
     *
     * @return bool
     * @since 2.0.0
     */
    public function canCapture()
    {
        return $this->getInvoice()->canCapture();
    }

    /**
     * Check if gateway is associated with invoice order
     *
     * @return bool
     * @since 2.0.0
     */
    public function isGatewayUsed()
    {
        return $this->getInvoice()->getOrder()->getPayment()->getMethodInstance()->isGateway();
    }

    /**
     * Check if new invoice emails can be sent
     *
     * @return bool
     * @since 2.0.0
     */
    public function canSendInvoiceEmail()
    {
        return $this->_salesData->canSendNewInvoiceEmail($this->getOrder()->getStore()->getId());
    }
}

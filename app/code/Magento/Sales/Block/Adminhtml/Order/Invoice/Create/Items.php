<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Block\Adminhtml\Order\Invoice\Create;

/**
 * Adminhtml invoice items grid
 */
class Items extends \Magento\Sales\Block\Adminhtml\Items\AbstractItems
{
    /**
     * Disable submit button
     *
     * @var bool
     */
    protected $_disableSubmitButton = false;

    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $_salesData;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Service\V1\StockItemService $stockItemService
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Data $salesData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Service\V1\StockItemService $stockItemService,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Data $salesData,
        array $data = array()
    ) {
        $this->_salesData = $salesData;
        parent::__construct($context, $stockItemService, $registry, $data);
    }

    /**
     * Prepare child blocks
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $onclick = "submitAndReloadArea($('invoice_item_container'),'" . $this->getUpdateUrl() . "')";
        $this->addChild(
            'update_button',
            'Magento\Backend\Block\Widget\Button',
            array('class' => 'update-button', 'label' => __('Update Qty\'s'), 'onclick' => $onclick)
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
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => $_submitLabel,
                'class' => 'save submit-button primary' . $submitButtonClass,
                'onclick' => 'disableElements(\'submit-button\');$(\'edit_form\').submit()',
                'disabled' => $this->_disableSubmitButton
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * Get is submit button disabled or not
     *
     * @return bool
     */
    public function getDisableSubmitButton()
    {
        return $this->_disableSubmitButton;
    }

    /**
     * Retrieve invoice order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getInvoice()->getOrder();
    }

    /**
     * Retrieve source
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getSource()
    {
        return $this->getInvoice();
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
     * Retrieve order totals block settings
     *
     * @return array
     */
    public function getOrderTotalData()
    {
        return array();
    }

    /**
     * Retrieve order totalbar block data
     *
     * @return array
     */
    public function getOrderTotalbarData()
    {
        $this->setPriceDataObject($this->getInvoice()->getOrder());

        $totalbarData = array();
        $totalbarData[] = array(__('Paid Amount'), $this->displayPriceAttribute('amount_paid'), false);
        $totalbarData[] = array(__('Refund Amount'), $this->displayPriceAttribute('amount_refunded'), false);
        $totalbarData[] = array(__('Shipping Amount'), $this->displayPriceAttribute('shipping_captured'), false);
        $totalbarData[] = array(__('Shipping Refund'), $this->displayPriceAttribute('shipping_refunded'), false);
        $totalbarData[] = array(__('Order Grand Total'), $this->displayPriceAttribute('grand_total'), true);
        return $totalbarData;
    }

    /**
     * Format price
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->getInvoice()->getOrder()->formatPrice($price);
    }

    /**
     * Get update button html
     *
     * @return string
     */
    public function getUpdateButtonHtml()
    {
        return $this->getChildHtml('update_button');
    }

    /**
     * Get update url
     *
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->getUrl('sales/*/updateQty', array('order_id' => $this->getInvoice()->getOrderId()));
    }

    /**
     * Check shipment availability for current invoice
     *
     * @return bool
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
     */
    public function isCaptureAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::capture');
    }

    /**
     * Check if invoice can be captured
     *
     * @return bool
     */
    public function canCapture()
    {
        return $this->getInvoice()->canCapture();
    }

    /**
     * Check if gateway is associated with invoice order
     *
     * @return bool
     */
    public function isGatewayUsed()
    {
        return $this->getInvoice()->getOrder()->getPayment()->getMethodInstance()->isGateway();
    }

    /**
     * Check if new invoice emails can be sent
     *
     * @return bool
     */
    public function canSendInvoiceEmail()
    {
        return $this->_salesData->canSendNewInvoiceEmail($this->getOrder()->getStore()->getId());
    }
}

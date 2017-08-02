<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create;

/**
 * Adminhtml credit memo items grid
 *
 * @api
 */
class Items extends \Magento\Sales\Block\Adminhtml\Items\AbstractItems
{
    /**
     * @var bool
     */
    protected $_canReturnToStock;

    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $_salesData;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Data $salesData
     * @param array $data
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
     */
    protected function _prepareLayout()
    {
        $onclick = "submitAndReloadArea($('creditmemo_item_container'),'" . $this->getUpdateUrl() . "')";
        $this->addChild(
            'update_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Update Qty\'s'), 'class' => 'update-button', 'onclick' => $onclick]
        );

        if ($this->getCreditmemo()->canRefund()) {
            if ($this->getCreditmemo()->getInvoice() && $this->getCreditmemo()->getInvoice()->getTransactionId()) {
                $this->addChild(
                    'submit_button',
                    \Magento\Backend\Block\Widget\Button::class,
                    [
                        'label' => __('Refund'),
                        'class' => 'save submit-button refund primary',
                        'onclick' => 'disableElements(\'submit-button\');submitCreditMemo()'
                    ]
                );
            }
            $this->addChild(
                'submit_offline',
                \Magento\Backend\Block\Widget\Button::class,
                [
                    'label' => __('Refund Offline'),
                    'class' => 'save submit-button primary',
                    'onclick' => 'disableElements(\'submit-button\');submitCreditMemoOffline()'
                ]
            );
        } else {
            $this->addChild(
                'submit_button',
                \Magento\Backend\Block\Widget\Button::class,
                [
                    'label' => __('Refund Offline'),
                    'class' => 'save submit-button primary',
                    'onclick' => 'disableElements(\'submit-button\');submitCreditMemoOffline()'
                ]
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieve invoice order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getCreditmemo()->getOrder();
    }

    /**
     * Retrieve source
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function getSource()
    {
        return $this->getCreditmemo();
    }

    /**
     * Retrieve order totals block settings
     *
     * @return array
     */
    public function getOrderTotalData()
    {
        return [];
    }

    /**
     * Retrieve order total bar block data
     *
     * @return array
     */
    public function getOrderTotalbarData()
    {
        $this->setPriceDataObject($this->getOrder());

        $totalBarData = [];
        $totalBarData[] = [__('Paid Amount'), $this->displayPriceAttribute('total_invoiced'), false];
        $totalBarData[] = [__('Refund Amount'), $this->displayPriceAttribute('total_refunded'), false];
        $totalBarData[] = [__('Shipping Amount'), $this->displayPriceAttribute('shipping_invoiced'), false];
        $totalBarData[] = [__('Shipping Refund'), $this->displayPriceAttribute('shipping_refunded'), false];
        $totalBarData[] = [__('Order Grand Total'), $this->displayPriceAttribute('grand_total'), true];
        return $totalBarData;
    }

    /**
     * Retrieve credit memo model instance
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function getCreditmemo()
    {
        return $this->_coreRegistry->registry('current_creditmemo');
    }

    /**
     * Check if allow to edit qty
     *
     * @return bool
     */
    public function canEditQty()
    {
        if ($this->getCreditmemo()->getOrder()->getPayment()->canRefund()) {
            return $this->getCreditmemo()->getOrder()->getPayment()->canRefundPartialPerInvoice();
        }
        return true;
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
        return $this->getUrl(
            'sales/*/updateQty',
            [
                'order_id' => $this->getCreditmemo()->getOrderId(),
                'invoice_id' => $this->getRequest()->getParam('invoice_id', null)
            ]
        );
    }

    /**
     * Whether to show 'Return to stock' column in creaditmemo grid
     *
     * @return bool
     */
    public function canReturnItemsToStock()
    {
        if ($this->_canReturnToStock === null) {
            $this->_canReturnToStock = $this->canReturnToStock();
            if ($this->_canReturnToStock) {
                $canReturnToStock = false;
                foreach ($this->getCreditmemo()->getAllItems() as $item) {
                    $productId = $item->getOrderItem()->getProductId();
                    $stockItem = $this->stockRegistry->getStockItem(
                        $productId,
                        $item->getOrderItem()->getStore()->getWebsiteId()
                    );
                    if ($stockItem->getManageStock()) {
                        $canReturnToStock = true;
                        $item->setCanReturnToStock($canReturnToStock);
                    } else {
                        $item->setCanReturnToStock(false);
                    }
                }
                $this->_canReturnToStock = $canReturnToStock;
                $this->getCreditmemo()->getOrder()->setCanReturnToStock($this->_canReturnToStock);
            }
        }
        return $this->_canReturnToStock;
    }

    /**
     * Check allow to send new credit memo email
     *
     * @return bool
     */
    public function canSendCreditmemoEmail()
    {
        return $this->_salesData->canSendNewCreditmemoEmail($this->getOrder()->getStore()->getId());
    }
}

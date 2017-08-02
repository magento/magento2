<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Block\Adminhtml\Create;

/**
 * Adminhtml shipment items grid
 *
 * @api
 * @since 2.0.0
 */
class Items extends \Magento\Sales\Block\Adminhtml\Items\AbstractItems
{
    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     * @since 2.0.0
     */
    protected $_salesData;

    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     * @since 2.0.0
     */
    protected $_carrierFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        array $data = []
    ) {
        $this->_salesData = $salesData;
        $this->_carrierFactory = $carrierFactory;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $data);
    }

    /**
     * Retrieve invoice order
     *
     * @return \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    public function getOrder()
    {
        return $this->getShipment()->getOrder();
    }

    /**
     * Retrieve source
     *
     * @return \Magento\Sales\Model\Order\Shipment
     * @since 2.0.0
     */
    public function getSource()
    {
        return $this->getShipment();
    }

    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     * @since 2.0.0
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }

    /**
     * Prepare child blocks
     *
     * @return string
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        $this->addChild(
            'submit_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Submit Shipment'),
                'class' => 'save submit-button primary',
                'onclick' => 'submitShipment(this);'
            ]
        );

        return parent::_beforeToHtml();
    }

    /**
     * Format given price
     *
     * @param float $price
     * @return string
     * @since 2.0.0
     */
    public function formatPrice($price)
    {
        return $this->getShipment()->getOrder()->formatPrice($price);
    }

    /**
     * Retrieve HTML of update button
     *
     * @return string
     * @since 2.0.0
     */
    public function getUpdateButtonHtml()
    {
        return $this->getChildHtml('update_button');
    }

    /**
     * Get url for update
     *
     * @return string
     * @since 2.0.0
     */
    public function getUpdateUrl()
    {
        return $this->getUrl('sales/*/updateQty', ['order_id' => $this->getShipment()->getOrderId()]);
    }

    /**
     * Check possibility to send shipment email
     *
     * @return bool
     * @since 2.0.0
     */
    public function canSendShipmentEmail()
    {
        return $this->_salesData->canSendNewShipmentEmail($this->getOrder()->getStore()->getId());
    }

    /**
     * Checks the possibility of creating shipping label by current carrier
     *
     * @return bool
     * @since 2.0.0
     */
    public function canCreateShippingLabel()
    {
        $shippingCarrier = $this->_carrierFactory->create(
            $this->getOrder()->getShippingMethod(true)->getCarrierCode()
        );
        return $shippingCarrier && $shippingCarrier->isShippingLabelsAvailable();
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Block\Adminhtml\Create;

use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Backend\Block\Widget\Button as WidgetButton;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Registry;
use Magento\Sales\Block\Adminhtml\Items\AbstractItems;
use Magento\Sales\Helper\Data as SalesHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment as OrderShipment;
use Magento\Shipping\Model\CarrierFactory;

/**
 * Adminhtml shipment items grid
 *
 * @api
 * @since 100.0.2
 */
class Items extends AbstractItems
{
    /**
     * Sales data
     *
     * @var SalesHelper
     */
    protected $_salesData;

    /**
     * @var CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * @param TemplateContext $context
     * @param StockRegistryInterface $stockRegistry
     * @param StockConfigurationInterface $stockConfiguration
     * @param Registry $registry
     * @param SalesHelper $salesData
     * @param CarrierFactory $carrierFactory
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        StockRegistryInterface $stockRegistry,
        StockConfigurationInterface $stockConfiguration,
        Registry $registry,
        SalesHelper $salesData,
        CarrierFactory $carrierFactory,
        array $data = []
    ) {
        $this->_salesData = $salesData;
        $this->_carrierFactory = $carrierFactory;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $data);
    }

    /**
     * Retrieve invoice order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->getShipment()->getOrder();
    }

    /**
     * Retrieve source
     *
     * @return OrderShipment
     */
    public function getSource()
    {
        return $this->getShipment();
    }

    /**
     * Retrieve shipment model instance
     *
     * @return OrderShipment
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }

    /**
     * Prepare child blocks
     *
     * @return string
     */
    protected function _beforeToHtml()
    {
        $this->addChild(
            'submit_button',
            WidgetButton::class,
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
     */
    public function formatPrice($price)
    {
        return $this->getShipment()->getOrder()->formatPrice($price);
    }

    /**
     * Retrieve HTML of update button
     *
     * @return string
     */
    public function getUpdateButtonHtml()
    {
        return $this->getChildHtml('update_button');
    }

    /**
     * Get url for update
     *
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->getUrl('sales/*/updateQty', ['order_id' => $this->getShipment()->getOrderId()]);
    }

    /**
     * Check possibility to send shipment email
     *
     * @return bool
     */
    public function canSendShipmentEmail()
    {
        return $this->_salesData->canSendNewShipmentEmail($this->getOrder()->getStore()->getId());
    }

    /**
     * Checks the possibility of creating shipping label by current carrier
     *
     * @return bool
     */
    public function canCreateShippingLabel()
    {
        $shippingCarrier = $this->_carrierFactory->create(
            $this->getOrder()->getShippingMethod(true)->getCarrierCode()
        );
        return $shippingCarrier && $shippingCarrier->isShippingLabelsAvailable();
    }
}

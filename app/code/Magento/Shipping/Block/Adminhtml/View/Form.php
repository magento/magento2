<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Shipment view form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Shipping\Block\Adminhtml\View;

use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Backend\Block\Widget\Button as WidgetButton;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Sales\Helper\Admin;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment as OrderShipment;
use Magento\Shipping\Helper\Data as ShippingHelper;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * @api
 * @since 100.0.2
 */
class Form extends AbstractOrder
{
    /**
     * @var CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * @param TemplateContext $context
     * @param Registry $registry
     * @param Admin $adminHelper
     * @param CarrierFactory $carrierFactory
     * @param array $data
     * @param ShippingHelper|null $shippingHelper
     * @param TaxHelper|null $taxHelper
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        Admin $adminHelper,
        CarrierFactory $carrierFactory,
        array $data = [],
        ?ShippingHelper $shippingHelper = null,
        ?TaxHelper $taxHelper = null
    ) {
        $this->_carrierFactory = $carrierFactory;
        $data['shippingHelper'] = $shippingHelper ?? ObjectManager::getInstance()->get(ShippingHelper::class);
        $data['taxHelper'] = $taxHelper ?? ObjectManager::getInstance()->get(TaxHelper::class);
        parent::__construct($context, $registry, $adminHelper, $data);
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
     * Get create label button html
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getCreateLabelButton()
    {
        $data['shipment_id'] = $this->getShipment()->getId();
        $url = $this->getUrl('adminhtml/order_shipment/createLabel', $data);
        return $this->getLayout()->createBlock(
            WidgetButton::class
        )->setData(
            [
                'label' => __('Create Shipping Label...'),
                'onclick' => 'packaging.showWindow();',
                'class' => 'action-create-label'
            ]
        )->toHtml();
    }

    /**
     * Get print label button html
     *
     * @return string
     */
    public function getPrintLabelButton()
    {
        $data['shipment_id'] = $this->getShipment()->getId();
        $url = $this->getUrl('adminhtml/order_shipment/printLabel', $data);
        return $this->getLayout()->createBlock(
            WidgetButton::class
        )->setData(
            ['label' => __('Print Shipping Label'), 'onclick' => 'setLocation(\'' . $url . '\')']
        )->toHtml();
    }

    /**
     * Show packages button html
     *
     * @return string
     */
    public function getShowPackagesButton()
    {
        return $this->getLayout()->createBlock(
            WidgetButton::class
        )->setData(
            ['label' => __('Show Packages'), 'onclick' => 'showPackedWindow();']
        )->toHtml();
    }

    /**
     * Check is carrier has functionality of creation shipping labels
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

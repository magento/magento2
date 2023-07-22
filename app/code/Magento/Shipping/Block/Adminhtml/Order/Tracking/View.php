<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Block\Adminhtml\Order\Tracking;

use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Backend\Block\Widget\Button as WidgetButton;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Shipment\Track as OrderShipmentTrack;
use Magento\Shipping\Block\Adminhtml\Order\Tracking;
use Magento\Shipping\Helper\Data as ShippingHelper;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Config;

/**
 * Shipment tracking control form
 *
 * @api
 * @since 100.0.2
 */
class View extends Tracking
{
    /**
     * @var CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * @param TemplateContext $context
     * @param Config $shippingConfig
     * @param Registry $registry
     * @param CarrierFactory $carrierFactory
     * @param array $data
     * @param ShippingHelper|null $shippingHelper
     */
    public function __construct(
        TemplateContext $context,
        Config $shippingConfig,
        Registry $registry,
        CarrierFactory $carrierFactory,
        array $data = [],
        ?ShippingHelper $shippingHelper = null
    ) {
        $data['shippingHelper'] = $shippingHelper ?? ObjectManager::getInstance()->get(ShippingHelper::class);
        parent::__construct($context, $shippingConfig, $registry, $data);
        $this->_carrierFactory = $carrierFactory;
    }

    /**
     * Prepares layout of block
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $onclick = "saveTrackingInfo($('shipment_tracking_info').parentNode, '" . $this->getSubmitUrl() . "')";
        $this->addChild(
            'save_button',
            WidgetButton::class,
            ['label' => __('Add'), 'class' => 'save', 'onclick' => $onclick]
        );
    }

    /**
     * Retrieve save url
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('adminhtml/*/addTrack/', ['shipment_id' => $this->getShipment()->getId()]);
    }

    /**
     * Retrieve save button html
     *
     * @return string
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * Retrieve remove url
     *
     * @param OrderShipmentTrack $track
     * @return string
     */
    public function getRemoveUrl($track)
    {
        return $this->getUrl(
            'adminhtml/*/removeTrack/',
            ['shipment_id' => $this->getShipment()->getId(), 'track_id' => $track->getId()]
        );
    }

    /**
     * Get carrier title
     *
     * @param string $code
     *
     * @return Phrase|string|bool
     */
    public function getCarrierTitle($code)
    {
        $carrier = $this->_carrierFactory->create($code);
        return $carrier ? $carrier->getConfigData('title') : __('Custom Value');
    }
}

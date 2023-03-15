<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Block\Tracking;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Html\Link as HtmlLink;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\Order;
use Magento\Shipping\Helper\Data as ShippingHelper;

/**
 * Tracking info link
 *
 * @api
 * @since 100.0.2
 */
class Link extends HtmlLink
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * Shipping data
     *
     * @var ShippingHelper
     */
    protected $_shippingData;

    /**
     * @param TemplateContext $context
     * @param Registry $registry
     * @param ShippingHelper $shippingData
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        ShippingHelper $shippingData,
        array $data = []
    ) {
        $this->_shippingData = $shippingData;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractModel $model
     * @return string
     */
    public function getWindowUrl($model)
    {
        return $this->_shippingData->getTrackingPopupUrlBySalesModel($model);
    }

    /**
     * Retrieve current order model instance
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }
}

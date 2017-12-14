<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sales order view items block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Shipping\Block;

/**
 * @api
 * @since 100.0.2
 */
class Items extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Zend Validator Uri
     *
     * @var \Zend\Validator\Uri
     */
    protected $_zendValidatorUri;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Zend\Validator\Uri $zendValidatorUri
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Zend\Validator\Uri $zendValidatorUri,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_zendValidatorUri = $zendValidatorUri;

        parent::__construct($context, $data);
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * @param object $shipment
     * @return string
     */
    public function getPrintShipmentUrl($shipment)
    {
        return $this->getUrl('*/*/printShipment', ['shipment_id' => $shipment->getId()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getPrintAllShipmentsUrl($order)
    {
        return $this->getUrl('*/*/printShipment', ['order_id' => $order->getId()]);
    }

    /**
     * Get html of shipment comments block
     *
     * @param   \Magento\Sales\Model\Order\Shipment $shipment
     * @return  string
     */
    public function getCommentsHtml($shipment)
    {
        $html = '';
        $comments = $this->getChildBlock('shipment_comments');
        if ($comments) {
            $comments->setEntity($shipment)->setTitle(__('About Your Shipment'));
            $html = $comments->toHtml();
        }
        return $html;
    }

    /**
     * Get Track Url for Track Item and return only if valid, absolute URI
     *
     * @param \Magento\Sales\Model\Order\Shipment\Track $track
     * @return string|bool
     */
    public function getValidTrackUrl($track){
        //\Zend_Uri::setConfig(array('allowRelative' => false, 'allowAbsolute' => false));
        if ($track->getTrackUrl() && \Zend_Uri::check($track->getTrackUrl())) {
            return $track->getTrackUrl();
        }
        return false;
    }
}

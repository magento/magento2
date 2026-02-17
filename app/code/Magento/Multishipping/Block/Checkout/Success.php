<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Multishipping checkout success information
 */
namespace Magento\Multishipping\Block\Checkout;

/**
 * @api
 * @since 100.0.2
 */
class Success extends \Magento\Multishipping\Block\Checkout\AbstractMultishipping
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        array $data = []
    ) {
        parent::__construct($context, $multishipping, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Get Order Ids
     *
     * @return array|bool|string
     */
    public function getOrderIds()
    {
        $ids = $this->_session->getOrderIds();
        if ($ids && is_array($ids)) {
            return $ids;
        }
        return false;
    }

    /**
     * Get order Url
     *
     * @param int $orderId
     * @return string
     */
    public function getViewOrderUrl($orderId)
    {
        return $this->getUrl('sales/order/view/', ['order_id' => $orderId, '_secure' => true]);
    }

    /**
     * Get continue Url
     *
     * @return string
     */
    public function getContinueUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
}

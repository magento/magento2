<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Multishipping checkout success information
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Multishipping\Block\Checkout;

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
     * @return array|bool|string
     */
    public function getOrderIds()
    {
        $ids = $this->_session->getOrderIds(true);
        if ($ids && is_array($ids)) {
            return $ids;
        }
        return false;
    }

    /**
     * @param int $orderId
     * @return string
     */
    public function getViewOrderUrl($orderId)
    {
        return $this->getUrl('sales/order/view/', ['order_id' => $orderId, '_secure' => true]);
    }

    /**
     * @return string
     */
    public function getContinueUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
}

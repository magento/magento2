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
namespace Magento\Sales\Block\Adminhtml\Order;

use Magento\Sales\Model\Order;

/**
 * Adminhtml order abstract block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class AbstractOrder extends \Magento\Backend\Block\Widget
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Admin helper
     *
     * @var \Magento\Sales\Helper\Admin
     */
    protected $_adminHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = array()
    ) {
        $this->_adminHelper = $adminHelper;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve available order
     *
     * @return Order
     * @throws \Magento\Framework\Model\Exception
     */
    public function getOrder()
    {
        if ($this->hasOrder()) {
            return $this->getData('order');
        }
        if ($this->_coreRegistry->registry('current_order')) {
            return $this->_coreRegistry->registry('current_order');
        }
        if ($this->_coreRegistry->registry('order')) {
            return $this->_coreRegistry->registry('order');
        }
        throw new \Magento\Framework\Model\Exception(__('We cannot get the order instance.'));
    }

    /**
     * Get price data object
     *
     * @return Order|mixed
     */
    public function getPriceDataObject()
    {
        $obj = $this->getData('price_data_object');
        if (is_null($obj)) {
            return $this->getOrder();
        }
        return $obj;
    }

    /**
     * Display price attribute
     *
     * @param string $code
     * @param bool $strong
     * @param string $separator
     * @return string
     */
    public function displayPriceAttribute($code, $strong = false, $separator = '<br/>')
    {
        return $this->_adminHelper->displayPriceAttribute($this->getPriceDataObject(), $code, $strong, $separator);
    }

    /**
     * Display prices
     *
     * @param float $basePrice
     * @param float $price
     * @param bool $strong
     * @param string $separator
     * @return string
     */
    public function displayPrices($basePrice, $price, $strong = false, $separator = '<br/>')
    {
        return $this->_adminHelper->displayPrices(
            $this->getPriceDataObject(),
            $basePrice,
            $price,
            $strong,
            $separator
        );
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
     * Retrieve order info block settings
     *
     * @return array
     */
    public function getOrderInfoData()
    {
        return array();
    }

    /**
     * Retrieve subtotal price include tax html formated content
     *
     * @param \Magento\Framework\Object $order
     * @return string
     */
    public function displayShippingPriceInclTax($order)
    {
        $shipping = $order->getShippingInclTax();
        if ($shipping) {
            $baseShipping = $order->getBaseShippingInclTax();
        } else {
            $shipping = $order->getShippingAmount() + $order->getShippingTaxAmount();
            $baseShipping = $order->getBaseShippingAmount() + $order->getBaseShippingTaxAmount();
        }
        return $this->displayPrices($baseShipping, $shipping, false, ' ');
    }
}

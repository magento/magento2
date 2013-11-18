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
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Block of links in Order view page
 */
namespace Magento\Sales\Block\Order\Info;

class Buttons extends \Magento\Core\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'order/info/buttons.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        $this->_customerSession = $customerSession;
        parent::__construct($coreData, $context, $data);
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
     * Get url for printing order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getPrintUrl($order)
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return $this->getUrl('sales/guest/print', array('order_id' => $order->getId()));
        }
        return $this->getUrl('sales/order/print', array('order_id' => $order->getId()));
    }

    /**
     * Get url for reorder action
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getReorderUrl($order)
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return $this->getUrl('sales/guest/reorder', array('order_id' => $order->getId()));
        }
        return $this->getUrl('sales/order/reorder', array('order_id' => $order->getId()));
    }
}

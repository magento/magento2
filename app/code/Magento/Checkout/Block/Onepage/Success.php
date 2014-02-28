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
 * @package     Magento_Checkout
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * One page checkout success page
 *
 * @category   Magento
 * @package    Magento_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Checkout\Block\Onepage;

class Success extends \Magento\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\RecurringProfile\Model\Resource\Profile\CollectionFactory
     */
    protected $_recurringProfileCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\RecurringProfile\Model\Resource\Profile\CollectionFactory $recurringProfileCollectionFactory
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\RecurringProfile\Model\Resource\Profile\CollectionFactory $recurringProfileCollectionFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        $this->_recurringProfileCollectionFactory = $recurringProfileCollectionFactory;
        $this->_orderConfig = $orderConfig;
        $this->_isScopePrivate = true;
    }

    /**
     * See if the order has state, visible on frontend
     *
     * @return bool
     */
    public function isOrderVisible()
    {
        return (bool)$this->_getData('is_order_visible');
    }

    /**
     * Render additional order information lines and return result html
     *
     * @return string
     */
    public function getAdditionalInfoHtml()
    {
        return $this->_layout->renderElement('order.success.additional.info');
    }

    /**
     * Getter for recurring profile view page
     *
     * @param \Magento\Object $profile
     * @return string
     */
    public function getProfileUrl(\Magento\Object $profile)
    {
        return $this->getUrl('sales/recurringProfile/view', array('profile' => $profile->getId()));
    }

    /**
     * Initialize data and prepare it for output
     *
     * @return string
     */
    protected function _beforeToHtml()
    {
        $this->_prepareLastOrder();
        $this->_prepareLastRecurringProfiles();
        return parent::_beforeToHtml();
    }

    /**
     * Get last order ID from session, fetch it and check whether it can be viewed, printed etc
     *
     * @return void
     */
    protected function _prepareLastOrder()
    {
        $orderId = $this->_checkoutSession->getLastOrderId();
        if ($orderId) {
            $order = $this->_orderFactory->create()->load($orderId);
            if ($order->getId()) {
                $isVisible = !in_array($order->getState(), $this->_orderConfig->getInvisibleOnFrontStates());
                $this->addData(array(
                    'is_order_visible' => $isVisible,
                    'view_order_url' => $this->getUrl('sales/order/view/', array('order_id' => $orderId)),
                    'print_url' => $this->getUrl('sales/order/print', array('order_id'=> $orderId)),
                    'can_print_order' => $isVisible,
                    'can_view_order'  => $this->_customerSession->isLoggedIn() && $isVisible,
                    'order_id'  => $order->getIncrementId(),
                ));
            }
        }
    }

    /**
     * Prepare recurring payment profiles from the session
     *
     * @return void
     */
    protected function _prepareLastRecurringProfiles()
    {
        $profileIds = $this->_checkoutSession->getLastRecurringProfileIds();
        if ($profileIds && is_array($profileIds)) {
            $collection = $this->_recurringProfileCollectionFactory->create()
                ->addFieldToFilter('profile_id', array('in' => $profileIds));
            $profiles = array();
            foreach ($collection as $profile) {
                $profiles[] = $profile;
            }
            if ($profiles) {
                $this->setRecurringProfiles($profiles);
                if ($this->_customerSession->isLoggedIn()) {
                    $this->setCanViewProfiles(true);
                }
            }
        }
    }
}

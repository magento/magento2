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
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer dashboard block
 *
 * @category   Magento
 * @package    Magento_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Block\Account;

class Dashboard extends \Magento\View\Element\Template
{
    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    protected $_subscription;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        array $data = array()
    ) {
        $this->_customerSession = $customerSession;
        $this->_subscriberFactory = $subscriberFactory;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    public function getCustomer()
    {
        return $this->_customerSession->getCustomer();
    }

    public function getAccountUrl()
    {
        return $this->_urlBuilder->getUrl('customer/account/edit', array('_secure'=>true));
    }

    public function getAddressesUrl()
    {
        return $this->_urlBuilder->getUrl('customer/address/index', array('_secure'=>true));
    }

    public function getAddressEditUrl($address)
    {
        return $this->_urlBuilder->getUrl('customer/address/edit', array('_secure'=>true, 'id'=>$address->getId()));
    }

    public function getOrdersUrl()
    {
        return $this->_urlBuilder->getUrl('customer/order/index', array('_secure'=>true));
    }

    public function getReviewsUrl()
    {
        return $this->_urlBuilder->getUrl('review/customer/index', array('_secure'=>true));
    }

    public function getWishlistUrl()
    {
        return $this->_urlBuilder->getUrl('customer/wishlist/index', array('_secure'=>true));
    }

    public function getSubscriptionObject()
    {
        if(is_null($this->_subscription)) {
            $this->_subscription = $this->_createSubscriber()->loadByCustomer($this->getCustomer());
        }

        return $this->_subscription;
    }

    public function getManageNewsletterUrl()
    {
        return $this->getUrl('newsletter/manage');
    }

    public function getSubscriptionText()
    {
        if($this->getSubscriptionObject()->isSubscribed()) {
            return __('You subscribe to our newsletter.');
        }

        return __('You are currently not subscribed to our newsletter.');
    }

    public function getPrimaryAddresses()
    {
        $addresses = $this->getCustomer()->getPrimaryAddresses();
        if (empty($addresses)) {
            return false;
        }
        return $addresses;
    }

    /**
     * Get back url in account dashboard
     *
     * This method is copypasted in:
     * \Magento\Wishlist\Block\Customer\Wishlist  - because of strange inheritance
     * \Magento\Customer\Block\Address\Book - because of secure url
     *
     * @return string
     */
    public function getBackUrl()
    {
        // the RefererUrl must be set in appropriate controller
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/');
    }

    /**
     * @return \Magento\Newsletter\Model\Subscriber
     */
    protected function _createSubscriber()
    {
        return $this->_subscriberFactory->create();
    }
}

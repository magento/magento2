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
namespace Magento\Customer\Block\Account;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Customer\Service\V1\CustomerAddressServiceInterface;

/**
 * Customer dashboard block
 */
class Dashboard extends \Magento\Framework\View\Element\Template
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
     * @var CustomerAccountServiceInterface
     */
    protected $_customerAccountService;

    /**
     * @var CustomerAddressServiceInterface
     */
    protected $_addressService;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param CustomerAccountServiceInterface $customerService
     * @param CustomerAddressServiceInterface $addressService
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        CustomerAccountServiceInterface $customerAccountService,
        CustomerAddressServiceInterface $addressService,
        array $data = array()
    ) {
        $this->_customerSession = $customerSession;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_customerAccountService = $customerAccountService;
        $this->_addressService = $addressService;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Return the Customer given the customer Id stored in the session.
     *
     * @return \Magento\Customer\Service\V1\Data\Customer
     */
    public function getCustomer()
    {
        return $this->_customerAccountService->getCustomer($this->_customerSession->getCustomerId());
    }

    /**
     * Retrieve the Url for editing the customer's account.
     *
     * @return string
     */
    public function getAccountUrl()
    {
        return $this->_urlBuilder->getUrl('customer/account/edit', array('_secure' => true));
    }

    /**
     * Retrieve the Url for customer addresses.
     *
     * @return string
     */
    public function getAddressesUrl()
    {
        return $this->_urlBuilder->getUrl('customer/address/index', array('_secure' => true));
    }

    /**
     * Retrieve the Url for editing the specified address.
     *
     * @param \Magento\Customer\Service\V1\Data\Address $address
     * @return string
     */
    public function getAddressEditUrl($address)
    {
        return $this->_urlBuilder->getUrl(
            'customer/address/edit',
            array('_secure' => true, 'id' => $address->getId())
        );
    }

    /**
     * Retrieve the Url for customer orders.
     *
     * @return string
     */
    public function getOrdersUrl()
    {
        return $this->_urlBuilder->getUrl('customer/order/index', array('_secure' => true));
    }

    /**
     * Retrieve the Url for customer reviews.
     *
     * @return string
     */
    public function getReviewsUrl()
    {
        return $this->_urlBuilder->getUrl('review/customer/index', array('_secure' => true));
    }

    /**
     * Retrieve the Url for managing customer wishlist.
     *
     * @return string
     */
    public function getWishlistUrl()
    {
        return $this->_urlBuilder->getUrl('customer/wishlist/index', array('_secure' => true));
    }

    /**
     * Retrieve the subscription object (i.e. the subscriber).
     *
     * @return \Magento\Newsletter\Model\Subscriber
     */
    public function getSubscriptionObject()
    {
        if (is_null($this->_subscription)) {
            $this->_subscription =
                $this->_createSubscriber()->loadByCustomerId($this->_customerSession->getCustomerId());
        }

        return $this->_subscription;
    }

    /**
     * Retrieve the Url for managing newsletter subscriptions.
     *
     * @return string
     */
    public function getManageNewsletterUrl()
    {
        return $this->getUrl('newsletter/manage');
    }

    /**
     * Retrieve subscription text, either subscribed or not.
     *
     * @return string
     */
    public function getSubscriptionText()
    {
        if ($this->getSubscriptionObject()->isSubscribed()) {
            return __('You subscribe to our newsletter.');
        }

        return __('You are currently not subscribed to our newsletter.');
    }

    /**
     * Retrieve the customer's primary addresses (i.e. default billing and shipping).
     *
     * @return \Magento\Customer\Service\V1\Data\Address[]|bool
     */
    public function getPrimaryAddresses()
    {
        $addresses = array();
        $customerId = $this->getCustomer()->getId();

        if ($defaultBilling = $this->_addressService->getDefaultBillingAddress($customerId)) {
            $addresses[] = $defaultBilling;
        }

        if ($defaultShipping = $this->_addressService->getDefaultShippingAddress($customerId)) {
            if ($defaultBilling) {
                if ($defaultBilling->getId() != $defaultShipping->getId()) {
                    $addresses[] = $defaultShipping;
                }
            } else {
                $addresses[] = $defaultShipping;
            }
        }

        return empty($addresses) ? false : $addresses;
    }

    /**
     * Get back Url in account dashboard.
     *
     * This method is copy/pasted in:
     * \Magento\Wishlist\Block\Customer\Wishlist  - Because of strange inheritance
     * \Magento\Customer\Block\Address\Book - Because of secure Url
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
     * Create an instance of a subscriber.
     *
     * @return \Magento\Newsletter\Model\Subscriber
     */
    protected function _createSubscriber()
    {
        return $this->_subscriberFactory->create();
    }
}

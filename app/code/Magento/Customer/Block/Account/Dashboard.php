<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Customer dashboard block
 *
 * @api
 * @since 2.0.0
 */
class Dashboard extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Newsletter\Model\Subscriber
     * @since 2.0.0
     */
    protected $subscription;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $customerSession;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     * @since 2.0.0
     */
    protected $subscriberFactory;

    /**
     * @var CustomerRepositoryInterface
     * @since 2.0.0
     */
    protected $customerRepository;

    /**
     * @var AccountManagementInterface
     * @since 2.0.0
     */
    protected $customerAccountManagement;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $customerAccountManagement
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->subscriberFactory = $subscriberFactory;
        $this->customerRepository = $customerRepository;
        $this->customerAccountManagement = $customerAccountManagement;
        parent::__construct($context, $data);
    }

    /**
     * Return the Customer given the customer Id stored in the session.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @since 2.0.0
     */
    public function getCustomer()
    {
        return $this->customerRepository->getById($this->customerSession->getCustomerId());
    }

    /**
     * Retrieve the Url for editing the customer's account.
     *
     * @return string
     * @since 2.0.0
     */
    public function getAccountUrl()
    {
        return $this->_urlBuilder->getUrl('customer/account/edit', ['_secure' => true]);
    }

    /**
     * Retrieve the Url for customer addresses.
     *
     * @return string
     * @since 2.0.0
     */
    public function getAddressesUrl()
    {
        return $this->_urlBuilder->getUrl('customer/address/index', ['_secure' => true]);
    }

    /**
     * Retrieve the Url for editing the specified address.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return string
     * @since 2.0.0
     */
    public function getAddressEditUrl($address)
    {
        return $this->_urlBuilder->getUrl(
            'customer/address/edit',
            ['_secure' => true, 'id' => $address->getId()]
        );
    }

    /**
     * Retrieve the Url for customer orders.
     *
     * @return string
     * @since 2.0.0
     */
    public function getOrdersUrl()
    {
        return $this->_urlBuilder->getUrl('customer/order/index', ['_secure' => true]);
    }

    /**
     * Retrieve the Url for customer reviews.
     *
     * @return string
     * @since 2.0.0
     */
    public function getReviewsUrl()
    {
        return $this->_urlBuilder->getUrl('review/customer/index', ['_secure' => true]);
    }

    /**
     * Retrieve the Url for managing customer wishlist.
     *
     * @return string
     * @since 2.0.0
     */
    public function getWishlistUrl()
    {
        return $this->_urlBuilder->getUrl('customer/wishlist/index', ['_secure' => true]);
    }

    /**
     * Retrieve the subscription object (i.e. the subscriber).
     *
     * @return \Magento\Newsletter\Model\Subscriber
     * @since 2.0.0
     */
    public function getSubscriptionObject()
    {
        if ($this->subscription === null) {
            $this->subscription =
                $this->_createSubscriber()->loadByCustomerId($this->customerSession->getCustomerId());
        }

        return $this->subscription;
    }

    /**
     * Retrieve the Url for managing newsletter subscriptions.
     *
     * @return string
     * @since 2.0.0
     */
    public function getManageNewsletterUrl()
    {
        return $this->getUrl('newsletter/manage');
    }

    /**
     * Retrieve subscription text, either subscribed or not.
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getSubscriptionText()
    {
        if ($this->getSubscriptionObject()->isSubscribed()) {
            return __('You are subscribed to our newsletter.');
        }

        return __('You aren\'t subscribed to our newsletter.');
    }

    /**
     * Retrieve the customer's primary addresses (i.e. default billing and shipping).
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]|bool
     * @since 2.0.0
     */
    public function getPrimaryAddresses()
    {
        $addresses = [];
        $customerId = $this->getCustomer()->getId();

        if ($defaultBilling = $this->customerAccountManagement->getDefaultBillingAddress($customerId)) {
            $addresses[] = $defaultBilling;
        }

        if ($defaultShipping = $this->customerAccountManagement->getDefaultShippingAddress($customerId)) {
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _createSubscriber()
    {
        return $this->subscriberFactory->create();
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Customer dashboard block
 *
 * @api
 * @since 100.0.2
 */
class Dashboard extends Template
{
    /**
     * @var Subscriber
     */
    protected $subscription;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param SubscriberFactory $subscriberFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $customerAccountManagement
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        SubscriberFactory $subscriberFactory,
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
     * @return CustomerInterface
     */
    public function getCustomer()
    {
        return $this->customerRepository->getById($this->customerSession->getCustomerId());
    }

    /**
     * Retrieve the Url for editing the customer's account.
     *
     * @return string
     */
    public function getAccountUrl()
    {
        return $this->_urlBuilder->getUrl('customer/account/edit', ['_secure' => true]);
    }

    /**
     * Retrieve the Url for customer addresses.
     *
     * @return string
     */
    public function getAddressesUrl()
    {
        return $this->_urlBuilder->getUrl('customer/address/index', ['_secure' => true]);
    }

    /**
     * Retrieve the Url for editing the specified address.
     *
     * @param AddressInterface $address
     * @return string
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
     * @deprecated 102.0.3 Action does not exist
     */
    public function getOrdersUrl()
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        trigger_error('Method is deprecated', E_USER_DEPRECATED);
        return '';
    }

    /**
     * Retrieve the Url for managing customer wishlist.
     *
     * @return string
     */
    public function getWishlistUrl()
    {
        return $this->_urlBuilder->getUrl('wishlist/index', ['_secure' => true]);
    }

    /**
     * Retrieve the subscription object (i.e. the subscriber).
     *
     * @return Subscriber
     */
    public function getSubscriptionObject()
    {
        if ($this->subscription === null) {
            $websiteId = (int)$this->_storeManager->getWebsite()->getId();
            $this->subscription = $this->_createSubscriber();
            $this->subscription->loadByCustomer((int)$this->getCustomer()->getId(), $websiteId);
        }

        return $this->subscription;
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
     * @return Phrase
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
     * @return AddressInterface[]|bool
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
     * @return Subscriber
     */
    protected function _createSubscriber()
    {
        return $this->subscriberFactory->create();
    }
}

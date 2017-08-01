<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Account\Dashboard;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Dashboard Customer Info
 *
 * @api
 * @since 2.0.0
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * Cached subscription object
     *
     * @var \Magento\Newsletter\Model\Subscriber
     * @since 2.0.0
     */
    protected $_subscription;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     * @since 2.0.0
     */
    protected $_subscriberFactory;

    /**
     * @var \Magento\Customer\Helper\View
     * @since 2.0.0
     */
    protected $_helperView;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     * @since 2.0.0
     */
    protected $currentCustomer;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Customer\Helper\View $helperView
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Helper\View $helperView,
        array $data = []
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_helperView = $helperView;
        parent::__construct($context, $data);
    }

    /**
     * Returns the Magento Customer Model for this block
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     * @since 2.0.0
     */
    public function getCustomer()
    {
        try {
            return $this->currentCustomer->getCustomer();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get the full name of a customer
     *
     * @return string full name
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->_helperView->getCustomerName($this->getCustomer());
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getChangePasswordUrl()
    {
        return $this->_urlBuilder->getUrl('customer/account/edit/changepass/1');
    }

    /**
     * Get Customer Subscription Object Information
     *
     * @return \Magento\Newsletter\Model\Subscriber
     * @since 2.0.0
     */
    public function getSubscriptionObject()
    {
        if (!$this->_subscription) {
            $this->_subscription = $this->_createSubscriber();
            $customer = $this->getCustomer();
            if ($customer) {
                $this->_subscription->loadByEmail($customer->getEmail());
            }
        }
        return $this->_subscription;
    }

    /**
     * Gets Customer subscription status
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getIsSubscribed()
    {
        return $this->getSubscriptionObject()->isSubscribed();
    }

    /**
     * Newsletter module availability
     *
     * @return bool
     * @since 2.0.0
     */
    public function isNewsletterEnabled()
    {
        return $this->getLayout()
            ->getBlockSingleton(\Magento\Customer\Block\Form\Register::class)
            ->isNewsletterEnabled();
    }

    /**
     * @return \Magento\Newsletter\Model\Subscriber
     * @since 2.0.0
     */
    protected function _createSubscriber()
    {
        return $this->_subscriberFactory->create();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        return $this->currentCustomer->getCustomerId() ? parent::_toHtml() : '';
    }
}

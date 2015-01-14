<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Account\Dashboard;

use Magento\Newsletter\Model\Subscriber;

/**
 * Dashboard newsletter info
 */
class Newsletter extends \Magento\Framework\View\Element\Template
{
    /**
     * The subscriber.
     *
     * @var Subscriber
     */
    protected $_subscription;

    /**
     * Session model.
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Factory for creating new Subscriber instances.
     *
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * Initialize the Dashboard\Newsletter instance.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_subscriberFactory = $subscriberFactory;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Fetch the subscription object. Create the subscriber by loading using the customerId.
     *
     * @return Subscriber
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
     * Use the factory to create an empty Subscriber model instance.
     *
     * @return Subscriber
     */
    protected function _createSubscriber()
    {
        return $this->_subscriberFactory->create();
    }
}

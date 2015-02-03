<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter subscribe controller
 */
namespace Magento\Newsletter\Controller;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Store\StoreManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Model\Url as CustomerUrl;

class Subscriber extends \Magento\Framework\App\Action\Action
{
    /**
     * Customer session
     *
     * @var Session
     */
    protected $_customerSession;

    /**
     * Subscriber factory
     *
     * @var SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var \Magento\Framework\Store\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CustomerUrl
     */
    protected $_customerUrl;

    /**
     * @param Context $context
     * @param SubscriberFactory $subscriberFactory
     * @param Session $customerSession
     * @param \Magento\Framework\Store\StoreManagerInterface $storeManager
     * @param CustomerUrl $customerUrl
     */
    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerUrl $customerUrl
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_customerSession = $customerSession;
        $this->_customerUrl = $customerUrl;
    }
}

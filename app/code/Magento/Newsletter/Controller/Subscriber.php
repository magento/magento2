<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter subscribe controller
 */
namespace Magento\Newsletter\Controller;

use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Model\Url as CustomerUrl;

/**
 * Class \Magento\Newsletter\Controller\Subscriber
 *
 * @since 2.0.0
 */
abstract class Subscriber extends \Magento\Framework\App\Action\Action
{
    /**
     * Customer session
     *
     * @var Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * Subscriber factory
     *
     * @var SubscriberFactory
     * @since 2.0.0
     */
    protected $_subscriberFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var CustomerUrl
     * @since 2.0.0
     */
    protected $_customerUrl;

    /**
     * @param Context $context
     * @param SubscriberFactory $subscriberFactory
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param CustomerUrl $customerUrl
     * @since 2.0.0
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

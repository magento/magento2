<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Plugin to change persistent session cart to guest cart on new customer register success.
 * @since 2.2.0
 */
class RemovePersistentCookieOnRegisterObserver implements ObserverInterface
{
    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     * @since 2.2.0
     */
    protected $_customerSession;

    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     * @since 2.2.0
     */
    protected $_persistentSession = null;

    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     * @since 2.2.0
     */
    protected $_persistentData = null;

    /**
     * @var \Magento\Persistent\Model\QuoteManager
     * @since 2.2.0
     */
    protected $quoteManager;

    /**
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Persistent\Model\QuoteManager $quoteManager
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Persistent\Model\QuoteManager $quoteManager
    ) {
        $this->quoteManager = $quoteManager;
        $this->_persistentSession = $persistentSession;
        $this->_persistentData = $persistentData;
        $this->_customerSession = $customerSession;
    }

    /**
     * Unset persistent cookie and make customer's quote as a guest
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.2.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_persistentData->canProcess($observer) || !$this->_persistentSession->isPersistent()) {
            return;
        }

        $this->_persistentSession->getSession()->removePersistentCookie();

        if (!$this->_customerSession->isLoggedIn()) {
            $this->_customerSession->setCustomerId(null)->setCustomerGroupId(null);
        }

        $this->quoteManager->setGuest();
    }
}

<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Persistent\Observer\SetLoadPersistentQuoteObserver
 *
 * @since 2.0.0
 */
class SetLoadPersistentQuoteObserver implements ObserverInterface
{
    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $_checkoutSession;

    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     * @since 2.0.0
     */
    protected $_persistentSession = null;

    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     * @since 2.0.0
     */
    protected $_persistentData = null;

    /**
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_persistentSession = $persistentSession;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_persistentData = $persistentData;
    }

    /**
     * Set quote to be loaded even if not active
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!(($this->_persistentSession->isPersistent() && !$this->_customerSession->isLoggedIn())
            && !$this->_persistentData->isShoppingCartPersist()
        )) {
            return;
        }

        if ($this->_checkoutSession) {
            $this->_checkoutSession->setLoadInactive();
        }
    }
}

<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Persistent\Observer\MakePersistentQuoteGuestObserver
 *
 * @since 2.0.0
 */
class MakePersistentQuoteGuestObserver implements ObserverInterface
{
    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

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
     * @var \Magento\Persistent\Model\QuoteManager
     * @since 2.0.0
     */
    protected $quoteManager;

    /**
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Persistent\Model\QuoteManager $quoteManager
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Persistent\Model\QuoteManager $quoteManager
    ) {
        $this->_persistentSession = $persistentSession;
        $this->_persistentData = $persistentData;
        $this->_customerSession = $customerSession;
        $this->quoteManager = $quoteManager;
    }

    /**
     * Make persistent quote to be guest
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $action \Magento\Persistent\Controller\Index */
        $action = $observer->getEvent()->getControllerAction();
        if ($action instanceof \Magento\Persistent\Controller\Index) {
            if ((($this->_persistentSession->isPersistent() && !$this->_customerSession->isLoggedIn())
                || $this->_persistentData->isShoppingCartPersist())
            ) {
                $this->quoteManager->setGuest(true);
            }
        }
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Persistent Session Observer
 * @since 2.0.0
 */
class SynchronizePersistentOnLogoutObserver implements ObserverInterface
{
    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     * @since 2.0.0
     */
    protected $_persistentSession;

    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     * @since 2.0.0
     */
    protected $_persistentData = null;

    /**
     * Session factory
     *
     * @var \Magento\Persistent\Model\SessionFactory
     * @since 2.0.0
     */
    protected $_sessionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Persistent\Model\SessionFactory $sessionFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Persistent\Model\SessionFactory $sessionFactory
    ) {
        $this->_persistentData = $persistentData;
        $this->_persistentSession = $persistentSession;
        $this->_sessionFactory = $sessionFactory;
    }

    /**
     * Unload persistent session (if set in config)
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(Observer $observer)
    {
        if (!$this->_persistentData->isEnabled() || !$this->_persistentData->getClearOnLogout()) {
            return;
        }

        $this->_sessionFactory->create()->removePersistentCookie();

        // Unset persistent session
        $this->_persistentSession->setSession(null);
    }
}

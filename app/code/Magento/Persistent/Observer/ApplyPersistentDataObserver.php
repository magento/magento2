<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Persistent\Observer\ApplyPersistentDataObserver
 *
 * @since 2.0.0
 */
class ApplyPersistentDataObserver implements ObserverInterface
{
    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * Persistent config factory
     *
     * @var \Magento\Persistent\Model\Persistent\ConfigFactory
     * @since 2.0.0
     */
    protected $_persistentConfigFactory;

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
     * @param \Magento\Persistent\Model\Persistent\ConfigFactory $persistentConfigFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Persistent\Model\Persistent\ConfigFactory $persistentConfigFactory
    ) {
        $this->_persistentSession = $persistentSession;
        $this->_persistentData = $persistentData;
        $this->_customerSession = $customerSession;
        $this->_persistentConfigFactory = $persistentConfigFactory;
    }

    /**
     * Apply persistent data
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_persistentData->canProcess($observer)
            || !$this->_persistentSession->isPersistent()
            || $this->_customerSession->isLoggedIn()
        ) {
            return $this;
        }
        /** @var \Magento\Persistent\Model\Persistent\Config $persistentConfig */
        $persistentConfig = $this->_persistentConfigFactory->create();
        $persistentConfig->setConfigFilePath($this->_persistentData->getPersistentConfigFilePath())->fire();
        return $this;
    }
}

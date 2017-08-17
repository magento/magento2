<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Persistent\Observer\ApplyBlockPersistentDataObserver
 *
 */
class ApplyBlockPersistentDataObserver implements ObserverInterface
{
    /**
     * Persistent config factory
     *
     * @var \Magento\Persistent\Model\Persistent\ConfigFactory
     */
    protected $_persistentConfigFactory;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     */
    protected $_persistentData = null;

    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSession = null;

    /**
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Persistent\Model\Persistent\ConfigFactory $persistentConfigFactory
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
     * Apply persistent data to specific block
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_persistentSession->isPersistent() || $this->_customerSession->isLoggedIn()) {
            return $this;
        }

        /** @var $block \Magento\Framework\View\Element\AbstractBlock */
        $block = $observer->getEvent()->getBlock();

        if (!$block) {
            return $this;
        }

        $configFilePath = $observer->getEvent()->getConfigFilePath();
        if (!$configFilePath) {
            $configFilePath = $this->_persistentData->getPersistentConfigFilePath();
        }

        /** @var $persistentConfig \Magento\Persistent\Model\Persistent\Config */
        $persistentConfig = $this->_persistentConfigFactory->create();
        $persistentConfig->setConfigFilePath($configFilePath);

        foreach ($persistentConfig->getBlockConfigInfo(get_class($block)) as $persistentConfigInfo) {
            $persistentConfig->fireOne($persistentConfigInfo, $block);
        }

        return $this;
    }
}

<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Store;

use Magento\TestFramework\App\Config;
use Magento\TestFramework\ObjectManager;

/**
 * Integration tests decoration of store manager
 *
 * @package Magento\TestFramework\Store
 */
class StoreManager implements \Magento\Store\Model\StoreManagerInterface
{
    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $decoratedStoreManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var null|bool
     */
    protected $fireEventInitCurrentStoreAfter = null;

    /**
     * @param \Magento\Store\Model\StoreManager $decoratedStoreManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Store\Model\StoreManager $decoratedStoreManager,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->decoratedStoreManager = $decoratedStoreManager;
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentStore($store)
    {
        $this->decoratedStoreManager->setCurrentStore($store);
        $this->dispatchInitCurrentStoreAfterEvent();
    }

    /**
     * {@inheritdoc}
     */
    public function setIsSingleStoreModeAllowed($value)
    {
        $this->decoratedStoreManager->setIsSingleStoreModeAllowed($value);
        $this->dispatchInitCurrentStoreAfterEvent();
    }

    /**
     * {@inheritdoc}
     */
    public function hasSingleStore()
    {
        $result = $this->decoratedStoreManager->hasSingleStore();
        $this->dispatchInitCurrentStoreAfterEvent();
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isSingleStoreMode()
    {
        $result = $this->decoratedStoreManager->isSingleStoreMode();
        $this->dispatchInitCurrentStoreAfterEvent();
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getStore($storeId = null)
    {
        $result = $this->decoratedStoreManager->getStore($storeId);
        $this->dispatchInitCurrentStoreAfterEvent();
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getStores($withDefault = false, $codeKey = false)
    {
        $result = $this->decoratedStoreManager->getStores($withDefault, $codeKey);
        $this->dispatchInitCurrentStoreAfterEvent();
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsite($websiteId = null)
    {
        $result = $this->decoratedStoreManager->getWebsite($websiteId);
        $this->dispatchInitCurrentStoreAfterEvent();
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsites($withDefault = false, $codeKey = false)
    {
        $result = $this->decoratedStoreManager->getWebsites($withDefault, $codeKey);
        $this->dispatchInitCurrentStoreAfterEvent();
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function reinitStores()
    {
        //In order to restore configFixture values
        $testAppConfig = ObjectManager::getInstance()->get(Config::class);
        $reflection = new \ReflectionClass($testAppConfig);
        $dataProperty = $reflection->getProperty('data');
        $dataProperty->setAccessible(true);
        $savedConfig = $dataProperty->getValue($testAppConfig);

        $this->decoratedStoreManager->reinitStores();

        $dataProperty->setValue($testAppConfig, $savedConfig);
        $this->dispatchInitCurrentStoreAfterEvent();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultStoreView()
    {
        $result = $this->decoratedStoreManager->getDefaultStoreView();
        $this->dispatchInitCurrentStoreAfterEvent();
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup($groupId = null)
    {
        $result = $this->decoratedStoreManager->getGroup($groupId);
        $this->dispatchInitCurrentStoreAfterEvent();
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups($withDefault = false)
    {
        $result = $this->decoratedStoreManager->getGroups($withDefault);
        $this->dispatchInitCurrentStoreAfterEvent();
        return $result;
    }

    /**
     * Dispatch event 'core_app_init_current_store_after'
     */
    protected function dispatchInitCurrentStoreAfterEvent()
    {
        if (null === $this->fireEventInitCurrentStoreAfter) {
            $this->fireEventInitCurrentStoreAfter = true;
            $this->eventManager->dispatch('core_app_init_current_store_after');
        }
    }
}

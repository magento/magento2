<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Store;

use Magento\TestFramework\App\Config;
use Magento\TestFramework\ObjectManager;

/**
 * Integration tests decoration of store manager
 */
class StoreManager implements \Magento\Store\Model\StoreManagerInterface
{
    private \Magento\Store\Model\StoreManager $decoratedStoreManager;
    private \Magento\Framework\Event\ManagerInterface $eventManager;
    private \Magento\TestFramework\App\Config $config;

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
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\TestFramework\App\Config $config
    ) {
        $this->decoratedStoreManager = $decoratedStoreManager;
        $this->eventManager = $eventManager;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function setCurrentStore($store)
    {
        $this->decoratedStoreManager->setCurrentStore($store);
        $this->dispatchInitCurrentStoreAfterEvent();
    }

    /**
     * @inheritdoc
     */
    public function setIsSingleStoreModeAllowed($value)
    {
        $this->decoratedStoreManager->setIsSingleStoreModeAllowed($value);
        $this->dispatchInitCurrentStoreAfterEvent();
    }

    /**
     * @inheritdoc
     */
    public function hasSingleStore()
    {
        $result = $this->decoratedStoreManager->hasSingleStore();
        $this->dispatchInitCurrentStoreAfterEvent();
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function isSingleStoreMode()
    {
        $result = $this->decoratedStoreManager->isSingleStoreMode();
        $this->dispatchInitCurrentStoreAfterEvent();
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getStore($storeId = null)
    {
        $result = $this->decoratedStoreManager->getStore($storeId);
        $this->dispatchInitCurrentStoreAfterEvent();
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getStores($withDefault = false, $codeKey = false)
    {
        $result = $this->decoratedStoreManager->getStores($withDefault, $codeKey);
        $this->dispatchInitCurrentStoreAfterEvent();
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getWebsite($websiteId = null)
    {
        $result = $this->decoratedStoreManager->getWebsite($websiteId);
        $this->dispatchInitCurrentStoreAfterEvent();
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getWebsites($withDefault = false, $codeKey = false)
    {
        $result = $this->decoratedStoreManager->getWebsites($withDefault, $codeKey);
        $this->dispatchInitCurrentStoreAfterEvent();
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function reinitStores()
    {
        //In order to restore configFixture values
        $reflection = new \ReflectionClass($this->config);

        if (\substr($reflection->getName(), -12) === '\Interceptor') {
            $dataProperty = $reflection->getParentClass()->getProperty('data');
        } else {
            $dataProperty = $reflection->getProperty('data');
        }

        $dataProperty->setAccessible(true);
        $savedConfig = $dataProperty->getValue($this->config);

        $this->decoratedStoreManager->reinitStores();

        $dataProperty->setValue($this->config, $savedConfig);
        $this->dispatchInitCurrentStoreAfterEvent();
    }

    /**
     * @inheritdoc
     */
    public function getDefaultStoreView()
    {
        $result = $this->decoratedStoreManager->getDefaultStoreView();
        $this->dispatchInitCurrentStoreAfterEvent();
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getGroup($groupId = null)
    {
        $result = $this->decoratedStoreManager->getGroup($groupId);
        $this->dispatchInitCurrentStoreAfterEvent();
        return $result;
    }

    /**
     * @inheritdoc
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

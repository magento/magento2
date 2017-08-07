<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Theme\Model\DesignConfigRepository;
use Magento\Theme\Api\Data\DesignConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class \Magento\Theme\Model\Design\Config\Plugin
 *
 * @since 2.1.0
 */
class Plugin
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.1.0
     */
    protected $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.1.0
     */
    protected $storeManager;

    /**
     * @param EventManager $eventManager
     * @param StoreManager $storeManager
     * @since 2.1.0
     */
    public function __construct(
        EventManager $eventManager,
        StoreManager $storeManager
    ) {
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
    }

    /**
     * @param DesignConfigRepository $subject
     * @param DesignConfigInterface $designConfig
     * @return DesignConfigInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function afterSave(DesignConfigRepository $subject, DesignConfigInterface $designConfig)
    {
        $website = in_array($designConfig->getScope(), [ScopeInterface::SCOPE_WEBSITE, ScopeInterface::SCOPE_WEBSITES])
            ? $this->storeManager->getWebsite($designConfig->getScopeId())
            : '';
        $store = in_array($designConfig->getScope(), [ScopeInterface::SCOPE_STORE, ScopeInterface::SCOPE_STORES])
            ? $this->storeManager->getStore($designConfig->getScopeId())
            : '';
        $this->eventManager->dispatch(
            'admin_system_config_changed_section_design',
            ['website' => $website, 'store' => $store]
        );
        return $designConfig;
    }

    /**
     * @param DesignConfigRepository $subject
     * @param DesignConfigInterface $designConfig
     * @return DesignConfigInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function afterDelete(DesignConfigRepository $subject, DesignConfigInterface $designConfig)
    {
        $website = in_array($designConfig->getScope(), [ScopeInterface::SCOPE_WEBSITE, ScopeInterface::SCOPE_WEBSITES])
            ? $this->storeManager->getWebsite($designConfig->getScopeId())
            : '';
        $store = in_array($designConfig->getScope(), [ScopeInterface::SCOPE_STORE, ScopeInterface::SCOPE_STORES])
            ? $this->storeManager->getStore($designConfig->getScopeId())
            : '';
        $this->eventManager->dispatch(
            'admin_system_config_changed_section_design',
            ['website' => $website, 'store' => $store]
        );
        return $designConfig;
    }
}

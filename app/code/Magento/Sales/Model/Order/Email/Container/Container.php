<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email\Container;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Container
 *
 * @api
 * @since 2.0.0
 */
abstract class Container implements IdentityInterface
{
    /**
     * @var StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @var Store
     * @since 2.0.0
     */
    protected $store;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $customerName;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $customerEmail;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @since 2.0.0
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Return store configuration value
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     * @since 2.0.0
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Set current store
     *
     * @param Store $store
     * @return void
     * @since 2.0.0
     */
    public function setStore(Store $store)
    {
        $this->store = $store;
    }

    /**
     * Return store
     *
     * @return Store
     * @since 2.0.0
     */
    public function getStore()
    {
        //current store
        if ($this->store instanceof Store) {
            return $this->store;
        }
        return $this->storeManager->getStore();
    }

    /**
     * Set customer name
     *
     * @param string $name
     * @return void
     * @since 2.0.0
     */
    public function setCustomerName($name)
    {
        $this->customerName = $name;
    }

    /**
     * Set customer email
     *
     * @param string $email
     * @return void
     * @since 2.0.0
     */
    public function setCustomerEmail($email)
    {
        $this->customerEmail = $email;
    }

    /**
     * Return customer name
     *
     * @return string
     * @since 2.0.0
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * Return customer email
     *
     * @return string
     * @since 2.0.0
     */
    public function getCustomerEmail()
    {
        return $this->customerEmail;
    }
}

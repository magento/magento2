<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\FrontController\Plugin;

use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreResolver\ReaderList;
use Magento\Store\Model\ScopeInterface;

/**
 * Plugin to set default store for admin area.
 */
class DefaultStore
{
    /**
     * Initialize dependencies.
     *
     * @param StoreManagerInterface $storeManager
     * @param ReaderList $readerList
     * @param string $runMode
     * @param null $scopeCode
     */
    public function __construct(
        protected readonly StoreManagerInterface $storeManager,
        protected readonly ReaderList $readerList,
        protected $runMode = ScopeInterface::SCOPE_STORE,
        protected $scopeCode = null
    ) {
        $this->runMode = $scopeCode ? $runMode : ScopeInterface::SCOPE_WEBSITE;
    }

    /**
     * Set current store for admin area
     *
     * @param FrontController $subject
     * @param RequestInterface $request
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(
        FrontController $subject,
        RequestInterface $request
    ) {
        $reader = $this->readerList->getReader($this->runMode);
        $defaultStoreId = $reader->getDefaultStoreId($this->scopeCode);
        $this->storeManager->setCurrentStore($defaultStoreId);
    }
}

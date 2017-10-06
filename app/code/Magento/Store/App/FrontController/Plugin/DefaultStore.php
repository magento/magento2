<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\App\FrontController\Plugin;

use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface as AppRequestInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreResolver\ReaderList;

/**
 * Plugin to set default store for admin area.
 */
class DefaultStore
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ReaderList
     */
    protected $readerList;

    /**
     * @var string
     */
    protected $runMode;

    /**
     * @var string
     */
    protected $scopeCode;

    /**
     * Initialize dependencies.
     *
     * @param StoreManagerInterface $storeManager
     * @param ReaderList $readerList
     * @param string $runMode
     * @param null $scopeCode
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ReaderList $readerList,
        $runMode = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        $this->runMode = $scopeCode ? $runMode : ScopeInterface::SCOPE_WEBSITE;
        $this->scopeCode = $scopeCode ? : Store::ADMIN_CODE;
        $this->readerList = $readerList;
        $this->storeManager = $storeManager;
    }

    /**
     * Set current store for admin area
     *
     * @param FrontController $subject
     * @param AppRequestInterface $request
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(
        FrontController $subject,
        AppRequestInterface $request
    ) {
        $reader = $this->readerList->getReader($this->runMode);
        $defaultStoreId = $reader->getDefaultStoreId($this->scopeCode);
        $this->storeManager->setCurrentStore($defaultStoreId);
    }
}

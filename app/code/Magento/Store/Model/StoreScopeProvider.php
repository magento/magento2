<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model;

use Magento\Framework\Model\Entity\ScopeProviderInterface;
use Magento\Store\Model\Store;
use Magento\Framework\Model\Entity\ScopeFactory;

/**
 * Class StoreScope
 */
class StoreScopeProvider implements ScopeProviderInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeFactory
     */
    private $scopeFactory;

    /**
     * StoreScopeProvider constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param ScopeFactory $scopeFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeFactory $scopeFactory
    ) {
        $this->storeManager = $storeManager;
        $this->scopeFactory = $scopeFactory;
    }

    /**
     * @return \Magento\Framework\Model\Entity\ScopeInterface
     */
    public function getContext()
    {
        $value = (int)$this->storeManager->getStore(true)->getId();
        $identifier = Store::STORE_ID;
        $fallback = null;
        if ($value == 1) {
            $value = 0;
        }
        if ($value != Store::DEFAULT_STORE_ID) {
            $fallback = $this->scopeFactory->create($identifier, Store::DEFAULT_STORE_ID);
        }
        return $this->scopeFactory->create($identifier, $value, $fallback);
    }
}

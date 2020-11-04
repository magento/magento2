<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SearchStorefront\Model\Store;

use Magento\Framework\Exception\LocalizedException;

class StoreManager implements \Magento\Store\Model\StoreManagerInterface
{
    const DEFAULT_EXCEPTION_MESSAGE = 'Currently StoreManager doesn\'t support %1';

    /**
     * @var StoreFactory
     */
    private $storeFactory;

    /**
     * @var Store
     */
    private $currentStore;

    /**
     * @param StoreFactory $storeFactory
     */
    public function __construct(
        StoreFactory $storeFactory
    ) {
        $this->setCurrentStore($storeFactory->create());
    }

    /**
     * @inheritDoc
     */
    public function setIsSingleStoreModeAllowed($value)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function hasSingleStore()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isSingleStoreMode()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getStore($storeId = null)
    {
        return $this->currentStore;
    }

    /**
     * @inheritDoc
     */
    public function getStores($withDefault = false, $codeKey = false)
    {
        return $this->currentStore ? [$this->currentStore] : [];
    }

    /**
     * @inheritDoc
     */
    public function getWebsite($websiteId = null)
    {
        throw new LocalizedException(__(self::DEFAULT_EXCEPTION_MESSAGE, 'website functionality'));
    }

    /**
     * @inheritDoc
     */
    public function getWebsites($withDefault = false, $codeKey = false)
    {
        throw new LocalizedException(__(self::DEFAULT_EXCEPTION_MESSAGE, 'website functionality'));
    }

    /**
     * @inheritDoc
     */
    public function reinitStores()
    {
        // TODO: Implement reinitStores() method.
    }

    /**
     * @inheritDoc
     */
    public function getDefaultStoreView()
    {
        return $this->currentStore;
    }

    /**
     * @inheritDoc
     */
    public function getGroup($groupId = null)
    {
        throw new LocalizedException(__(self::DEFAULT_EXCEPTION_MESSAGE, 'store group functionality'));
    }

    /**
     * @inheritDoc
     */
    public function getGroups($withDefault = false)
    {
        throw new LocalizedException(__(self::DEFAULT_EXCEPTION_MESSAGE, 'store group functionality'));
    }

    /**
     * @inheritDoc
     */
    public function setCurrentStore($store)
    {
        $this->currentStore = $store;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Config;

/**
 * Information Expert in stores handling
 * @since 2.0.0
 */
class StoreRepository implements \Magento\Store\Api\StoreRepositoryInterface
{
    /**
     * @var StoreFactory
     * @since 2.0.0
     */
    protected $storeFactory;

    /**
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     * @since 2.0.0
     */
    protected $storeCollectionFactory;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface[]
     * @since 2.0.0
     */
    protected $entities = [];

    /**
     * @var \Magento\Store\Api\Data\StoreInterface[]
     * @since 2.0.0
     */
    protected $entitiesById = [];

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $allLoaded = false;

    /**
     * @var Config
     * @since 2.2.0
     */
    private $appConfig;

    /**
     * @param StoreFactory $storeFactory
     * @param \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory
     * @since 2.0.0
     */
    public function __construct(
        StoreFactory $storeFactory,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory
    ) {
        $this->storeFactory = $storeFactory;
        $this->storeCollectionFactory = $storeCollectionFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function get($code)
    {
        if (isset($this->entities[$code])) {
            return $this->entities[$code];
        }

        $storeData = $this->getAppConfig()->get('scopes', "stores/$code", []);
        $store = $this->storeFactory->create([
            'data' => $storeData
        ]);

        if ($store->getId() === null) {
            throw new NoSuchEntityException(__('Requested store is not found'));
        }
        $this->entities[$code] = $store;
        $this->entitiesById[$store->getId()] = $store;
        return $store;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getActiveStoreByCode($code)
    {
        $store = $this->get($code);

        if (!$store->isActive()) {
            throw new StoreIsInactiveException();
        }
        return $store;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getById($id)
    {
        if (isset($this->entitiesById[$id])) {
            return $this->entitiesById[$id];
        }

        $storeData = $this->getAppConfig()->get('scopes', "stores/$id", []);
        $store = $this->storeFactory->create([
            'data' => $storeData
        ]);

        if ($store->getId() === null) {
            throw new NoSuchEntityException(__('Requested store is not found'));
        }

        $this->entitiesById[$id] = $store;
        $this->entities[$store->getCode()] = $store;
        return $store;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getActiveStoreById($id)
    {
        $store = $this->getById($id);

        if (!$store->isActive()) {
            throw new StoreIsInactiveException();
        }
        return $store;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getList()
    {
        if ($this->allLoaded) {
            return $this->entities;
        }
        $stores = $this->getAppConfig()->get('scopes', "stores", []);
        foreach ($stores as $data) {
            $store = $this->storeFactory->create([
                'data' => $data
            ]);
            $this->entities[$store->getCode()] = $store;
            $this->entitiesById[$store->getId()] = $store;
        }
        $this->allLoaded = true;
        return $this->entities;
    }

    /**
     * Retrieve application config.
     *
     * @deprecated 2.2.0
     * @return Config
     * @since 2.2.0
     */
    private function getAppConfig()
    {
        if (!$this->appConfig) {
            $this->appConfig = ObjectManager::getInstance()->get(Config::class);
        }
        return $this->appConfig;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function clean()
    {
        $this->entities = [];
        $this->entitiesById = [];
        $this->allLoaded = false;
    }
}

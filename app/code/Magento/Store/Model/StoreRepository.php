<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Config;

/**
 * Information Expert in stores handling
 *
 * @package Magento\Store\Model
 */
class StoreRepository implements \Magento\Store\Api\StoreRepositoryInterface
{
    /**
     * @var StoreFactory
     */
    protected $storeFactory;

    /**
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     */
    protected $storeCollectionFactory;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface[]
     */
    protected $entities = [];

    /**
     * @var \Magento\Store\Api\Data\StoreInterface[]
     */
    protected $entitiesById = [];

    /**
     * @var bool
     */
    protected $allLoaded = false;

    /**
     * @var Config
     */
    private $appConfig;

    /**
     * @param StoreFactory $storeFactory
     * @param \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory
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
     */
    public function getById($id)
    {
        if (isset($this->entitiesById[$id])) {
            return $this->entitiesById[$id];
        }

        $storeData = [];
        $stores = $this->getAppConfig()->get('scopes', "stores", []);
        foreach ($stores as $data) {
            if (isset($data['store_id']) && $data['store_id'] == $id) {
                $storeData = $data;
                break;
            }
        }
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
     * @deprecated
     * @return Config
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
     */
    public function clean()
    {
        $this->entities = [];
        $this->entitiesById = [];
        $this->allLoaded = false;
    }
}

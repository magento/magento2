<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\Exception\NoSuchEntityException;

class StoreRepository implements \Magento\Store\Api\StoreRepositoryInterface
{
    /**
     * @var StoreFactory
     */
    protected $storeFactory;

    /**
     * @var \Magento\Store\Model\Resource\Store\CollectionFactory
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
     * @param StoreFactory $storeFactory
     * @param \Magento\Store\Model\Resource\Store\CollectionFactory $storeCollectionFactory
     */
    public function __construct(
        StoreFactory $storeFactory,
        \Magento\Store\Model\Resource\Store\CollectionFactory $storeCollectionFactory
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
        $store = $this->storeFactory->create();
        $store->load($code, 'code');
        if ($store->getId() === null) {
            // TODO: MAGETWO-39826 Need to replace on NoSuchEntityException
            throw new \InvalidArgumentException();
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
        $store = $this->storeFactory->create();
        $store->load($id);
        if ($store->getId() === null) {
            throw new NoSuchEntityException();
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
        if (!$this->allLoaded) {
            /** @var $storeCollection \Magento\Store\Model\Resource\Store\Collection */
            $storeCollection = $this->storeCollectionFactory->create();
            $storeCollection->setLoadDefault(true);
            foreach ($storeCollection as $item) {
                $this->entities[$item->getCode()] = $item;
                $this->entitiesById[$item->getId()] = $item;
            }
            $this->allLoaded = true;
        }
        return $this->entities;
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

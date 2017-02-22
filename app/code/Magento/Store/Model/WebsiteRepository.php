<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;

class WebsiteRepository implements \Magento\Store\Api\WebsiteRepositoryInterface
{
    /**
     * @var WebsiteFactory
     */
    protected $factory;

    /**
     * @var CollectionFactory
     */
    protected $websiteCollectionFactory;

    /**
     * @var \Magento\Store\Api\Data\WebsiteInterface[]
     */
    protected $entities = [];

    /**
     * @var \Magento\Store\Api\Data\WebsiteInterface[]
     */
    protected $entitiesById = [];

    /**
     * @var bool
     */
    protected $allLoaded = false;

    /**
     * @var \Magento\Store\Api\Data\WebsiteInterface[]
     */
    protected $default;

    /**
     * @param WebsiteFactory $factory
     * @param CollectionFactory $websiteCollectionFactory
     */
    public function __construct(
        WebsiteFactory $factory,
        CollectionFactory $websiteCollectionFactory
    ) {
        $this->factory = $factory;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get($code)
    {
        if (isset($this->entities[$code])) {
            return $this->entities[$code];
        }
        $website = $this->factory->create();
        $website->load($code, 'code');
        if ($website->getId() === null) {
            throw new NoSuchEntityException();
        }
        $this->entities[$code] = $website;
        $this->entitiesById[$website->getId()] = $website;
        return $website;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        if (isset($this->entitiesById[$id])) {
            return $this->entitiesById[$id];
        }
        /** @var Website $website */
        $website = $this->factory->create();
        $website->load($id);
        if ($website->getId() === null) {
            throw new NoSuchEntityException();
        }
        $this->entitiesById[$id] = $website;
        $this->entities[$website->getCode()] = $website;
        return $website;
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        if (!$this->allLoaded) {
            $collection = $this->websiteCollectionFactory->create();
            $collection->setLoadDefault(true);
            foreach ($collection as $item) {
                $this->entities[$item->getCode()] = $item;
            }
            $this->allLoaded = true;
        }
        return $this->entities;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefault()
    {
        if (!$this->default) {
            foreach ($this->entities as $entity) {
                if ($entity->getIsDefault()) {
                    $this->default = $entity;
                    return $this->default;
                }
            }
            if (!$this->allLoaded) {
                /** @var \Magento\Store\Model\ResourceModel\Website\Collection $collection */
                $collection = $this->websiteCollectionFactory->create();
                $collection->addFieldToFilter('is_default', 1);
                $items = $collection->getItems();
                if (count($items) > 1) {
                    throw new \DomainException(__('More than one default website is defined'));
                }
                if (count($items) === 0) {
                    throw new \DomainException(__('Default website is not defined'));
                }
                $this->default = $collection->getFirstItem();
                $this->entities[$this->default->getCode()] = $this->default;
                $this->entitiesById[$this->default->getId()] = $this->default;
            } else {
                throw new \DomainException(__('Default website is not defined'));
            }
        }
        return $this->default;
    }

    /**
     * {@inheritdoc}
     */
    public function clean()
    {
        $this->entities = [];
        $this->entitiesById = [];
        $this->default = null;
        $this->allLoaded = false;
    }
}

<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;
use Magento\Framework\App\Config;

/**
 * Information Expert in store websites handling
 *
 * @package Magento\Store\Model
 */
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
     * @var Config
     */
    private $appConfig;

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

        $websiteData = $this->getAppConfig()->get('scopes', "websites/$code", []);
        $website = $this->factory->create([
            'data' => $websiteData
        ]);

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

        $websiteData = $this->getAppConfig()->get('scopes', "websites/$id", []);
        $website = $this->factory->create([
            'data' => $websiteData
        ]);

        if ($website->getId() === null) {
            throw new NoSuchEntityException();
        }
        $this->entities[$website->getCode()] = $website;
        $this->entitiesById[$id] = $website;
        return $website;
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        if (!$this->allLoaded) {
            $websites = $this->getAppConfig()->get('scopes', 'websites', []);
            foreach ($websites as $data) {
                $website = $this->factory->create([
                    'data' => $data
                ]);
                $this->entities[$website->getCode()] = $website;
                $this->entitiesById[$website->getId()] = $website;
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
                $this->initDefaultWebsite();
            }
            if (!$this->default) {
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
     * Initialize default website.
     * @return void
     */
    private function initDefaultWebsite()
    {
        $websites = (array) $this->getAppConfig()->get('scopes', 'websites', []);
        foreach ($websites as $data) {
            if (isset($data['is_default']) && $data['is_default'] == 1) {
                if ($this->default) {
                    throw new \DomainException(__('More than one default website is defined'));
                }
                $website = $this->factory->create([
                    'data' => $data
                ]);
                $this->default = $website;
                $this->entities[$this->default->getCode()] = $this->default;
                $this->entitiesById[$this->default->getId()] = $this->default;
            }
        }
    }
}

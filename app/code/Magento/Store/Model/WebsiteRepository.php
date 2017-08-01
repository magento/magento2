<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;
use Magento\Framework\App\Config;

/**
 * Information Expert in store websites handling
 * @since 2.0.0
 */
class WebsiteRepository implements \Magento\Store\Api\WebsiteRepositoryInterface
{
    /**
     * @var WebsiteFactory
     * @since 2.0.0
     */
    protected $factory;

    /**
     * @var CollectionFactory
     * @since 2.0.0
     */
    protected $websiteCollectionFactory;

    /**
     * @var \Magento\Store\Api\Data\WebsiteInterface[]
     * @since 2.0.0
     */
    protected $entities = [];

    /**
     * @var \Magento\Store\Api\Data\WebsiteInterface[]
     * @since 2.0.0
     */
    protected $entitiesById = [];

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $allLoaded = false;

    /**
     * @var \Magento\Store\Api\Data\WebsiteInterface[]
     * @since 2.0.0
     */
    protected $default;

    /**
     * @var Config
     * @since 2.2.0
     */
    private $appConfig;

    /**
     * @param WebsiteFactory $factory
     * @param CollectionFactory $websiteCollectionFactory
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * Initialize default website.
     * @return void
     * @since 2.2.0
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

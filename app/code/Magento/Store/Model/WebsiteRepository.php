<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model;

use Magento\Framework\App\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;

/**
 * Information Expert in store websites handling
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
     * @inheritdoc
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
            throw new NoSuchEntityException(
                __(
                    sprintf(
                        "The website with code %s that was requested wasn't found. Verify the website and try again.",
                        $code
                    )
                )
            );
        }
        $this->entities[$code] = $website;
        $this->entitiesById[$website->getId()] = $website;
        return $website;
    }

    /**
     * @inheritdoc
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
            throw new NoSuchEntityException(
                __(
                    sprintf(
                        "The website with id %s that was requested wasn't found. Verify the website and try again.",
                        $id
                    )
                )
            );
        }
        $this->entities[$website->getCode()] = $website;
        $this->entitiesById[$id] = $website;
        return $website;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
                throw new \DomainException(__("The default website isn't defined. Set the website and try again."));
            }
        }

        return $this->default;
    }

    /**
     * @inheritdoc
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
     * @deprecated 100.1.3
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
     *
     * @return void
     */
    private function initDefaultWebsite()
    {
        $websites = (array) $this->getAppConfig()->get('scopes', 'websites', []);
        foreach ($websites as $data) {
            if (isset($data['is_default']) && $data['is_default'] == 1) {
                if ($this->default) {
                    throw new \DomainException(
                        __(
                            'The default website is invalid. '
                            . 'Make sure no more than one default is defined and try again.'
                        )
                    );
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

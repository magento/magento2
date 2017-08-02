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
 * Information Expert in store groups handling
 *
 * @package Magento\Store\Model
 * @since 2.0.0
 */
class GroupRepository implements \Magento\Store\Api\GroupRepositoryInterface
{
    /**
     * @var GroupFactory
     * @since 2.0.0
     */
    protected $groupFactory;

    /**
     * @var \Magento\Store\Api\Data\GroupInterface[]
     * @since 2.0.0
     */
    protected $entities = [];

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $allLoaded = false;

    /**
     * @var \Magento\Store\Model\ResourceModel\Group\CollectionFactory
     * @since 2.0.0
     */
    protected $groupCollectionFactory;

    /**
     * @var Config
     * @since 2.2.0
     */
    private $appConfig;

    /**
     * @param GroupFactory $groupFactory
     * @param \Magento\Store\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory
     * @since 2.0.0
     */
    public function __construct(
        GroupFactory $groupFactory,
        \Magento\Store\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory
    ) {
        $this->groupFactory = $groupFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function get($id)
    {
        if (isset($this->entities[$id])) {
            return $this->entities[$id];
        }

        $group = $this->groupFactory->create([
            'data' => $this->getAppConfig()->get('scopes', "groups/$id", [])
        ]);

        if (null === $group->getId()) {
            throw new NoSuchEntityException();
        }
        $this->entities[$id] = $group;
        return $group;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getList()
    {
        if (!$this->allLoaded) {
            $groups = $this->getAppConfig()->get('scopes', 'groups', []);
            foreach ($groups as $data) {
                $group = $this->groupFactory->create([
                    'data' => $data
                ]);
                $this->entities[$group->getId()] = $group;
            }
            $this->allLoaded = true;
        }

        return $this->entities;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function clean()
    {
        $this->entities = [];
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
}

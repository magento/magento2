<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Fixture;

use Magento\Catalog\Helper\DefaultCategory;
use Magento\Framework\DataObject;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\GroupInterfaceFactory;
use Magento\Store\Model\ResourceModel\Group as GroupResource;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class Group implements DataFixtureInterface
{
    private const DEFAULT_DATA = [
        'code' => 'test_store_group%uniqid%',
        'name' => 'Test Store Group%uniqid%',
    ];

    /**
     * @var GroupInterfaceFactory
     */
    private $groupFactory;

    /**
     * @var GroupResource
     */
    private $groupResource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DefaultCategory
     */
    private $defaultCategory;

    /**
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @param GroupInterfaceFactory $groupFactory
     * @param GroupResource $groupResource
     * @param StoreManagerInterface $storeManager
     * @param DefaultCategory $defaultCategory
     * @param ProcessorInterface $dataProcessor
     */
    public function __construct(
        GroupInterfaceFactory $groupFactory,
        GroupResource $groupResource,
        StoreManagerInterface $storeManager,
        DefaultCategory $defaultCategory,
        ProcessorInterface $dataProcessor
    ) {
        $this->groupFactory = $groupFactory;
        $this->groupResource = $groupResource;
        $this->storeManager = $storeManager;
        $this->defaultCategory = $defaultCategory;
        $this->dataProcessor = $dataProcessor;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * <pre>
     *    $data = [
     *      'id'               => (int) ID. Optional.
     *      'code'             => (string) Code. Optional.
     *      'name'             => (string) Name. Optional.
     *      'website_id'       => (int) Website ID. Optional. Default: default website.
     *      'root_category_id' => (int) Root Category ID. Optional. Default: default root category.
     *      'default_store_id' => (int) Default Store ID. Optional.
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        /** @var GroupInterface $group */
        $group = $this->groupFactory->create();
        $group->setData($this->prepareData($data));
        $this->groupResource->save($group);
        $this->storeManager->reinitStores();

        return $group;
    }

    /**
     * Prepare store group data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $defaultData = self::DEFAULT_DATA;
        $defaultData['root_category_id'] = $this->defaultCategory->getId();
        $defaultData['website_id'] = $this->storeManager->getDefaultStoreView()->getWebsiteId();
        $data = array_merge($defaultData, $data);

        return $this->dataProcessor->process($this, $data);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\StoreInterfaceFactory;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Db\Sequence;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class Store implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'code' => 'test_store_view%uniqid%',
        'name' => 'Test Store View%uniqid%',
        'sort_order' => '0',
        'is_active' => '1'
    ];

    /**
     * @var StoreInterfaceFactory
     */
    private $storeFactory;

    /**
     * @var StoreResource
     */
    private $storeResource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @var Sequence
     */
    private $sequence;

    /**
     * @param StoreInterfaceFactory $storeFactory
     * @param StoreResource $storeResource
     * @param StoreManagerInterface $storeManager
     * @param ProcessorInterface $dataProcessor
     * @param Sequence $sequence
     */
    public function __construct(
        StoreInterfaceFactory $storeFactory,
        StoreResource $storeResource,
        StoreManagerInterface $storeManager,
        ProcessorInterface $dataProcessor,
        Sequence $sequence
    ) {
        $this->storeFactory = $storeFactory;
        $this->storeResource = $storeResource;
        $this->storeManager = $storeManager;
        $this->dataProcessor = $dataProcessor;
        $this->sequence = $sequence;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * <pre>
     *    $data = [
     *      'id'             => (int) ID. Optional.
     *      'code'           => (string) Code. Optional.
     *      'name'           => (string) Name. Optional.
     *      'website_id'     => (int) Website ID. Optional. Default: default website.
     *      'store_group_id' => (int) Store Group ID. Optional. Default: default store group.
     *      'is_active'      => (int) Is Active. Optional. Default: 1
     *      'sort_order'     => (int) Sort Order. Optional. Default: 0
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        /** @var StoreInterface $store */
        $store = $this->storeFactory->create();
        $store->setData($this->prepareData($data));
        $this->storeResource->save($store);
        $this->storeManager->reinitStores();
        $this->regenerateSequenceTables((int)$store->getId());
        return $store;
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        /** @var StoreInterface $store */
        $store = $this->storeFactory->create();
        $this->storeResource->load($store, $data->getCode(), 'code');
        if ($store->getId()) {
            $this->storeResource->delete($store);
        }
        $this->storeManager->reinitStores();
    }

    /**
     * Prepare store data
     *
     * @param array $data
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function prepareData(array $data): array
    {
        $data = array_merge(self::DEFAULT_DATA, $data);

        if (!isset($data['store_group_id']) && !isset($data['website_id'])) {
            $data['store_group_id'] = $this->storeManager->getDefaultStoreView()->getStoreGroupId();
        }
        if (isset($data['store_group_id']) && !isset($data['website_id'])) {
            $data['website_id'] = $this->storeManager->getGroup($data['store_group_id'])->getWebsiteId();
        } elseif (!isset($data['store_group_id']) && isset($data['website_id'])) {
            $data['store_group_id'] = $this->storeManager->getWebsite($data['website_id'])->getDefaultGroupId();
        }
        $data['group_id'] = $data['store_group_id'];

        return $this->dataProcessor->process($this, $data);
    }

    /**
     * Generate missing sequence tables
     *
     * @param int $storeId
     *
     * @return void
     */
    private function regenerateSequenceTables(int $storeId): void
    {
        if ($storeId >= 10) {
            $n = $storeId + 1;
            $this->sequence->generateSequences($n);
        }
    }
}

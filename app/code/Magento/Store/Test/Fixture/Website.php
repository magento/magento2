<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\Store\Api\Data\WebsiteInterfaceFactory;
use Magento\Store\Model\ResourceModel\Website as WebsiteResource;

class Website implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'code' => 'test_website%uniqid%',
        'name' => 'Test Website%uniqid%',
        'is_default' => '0'
    ];

    /**
     * @var WebsiteInterfaceFactory
     */
    private $websiteFactory;

    /**
     * @var WebsiteResource
     */
    private $websiteResource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @param WebsiteInterfaceFactory $websiteFactory
     * @param WebsiteResource $websiteResource
     * @param StoreManagerInterface $storeManager
     * @param ProcessorInterface $dataProcessor
     */
    public function __construct(
        WebsiteInterfaceFactory $websiteFactory,
        WebsiteResource $websiteResource,
        StoreManagerInterface $storeManager,
        ProcessorInterface $dataProcessor
    ) {
        $this->websiteFactory = $websiteFactory;
        $this->websiteResource = $websiteResource;
        $this->storeManager = $storeManager;
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
     *      'default_group_id' => (int) Default Group ID. Optional.
     *      'is_default'       => (int) Is Default. Optional. Default: 0.
     *      'sort_order'       => (int) Sort Order. Optional. Default: 0.
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        /** @var WebsiteInterface $website */
        $website = $this->websiteFactory->create();
        $website->setData($this->prepareData($data));
        $this->websiteResource->save($website);
        $this->storeManager->reinitStores();

        return $website;
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        /** @var WebsiteInterface $website */
        $website = $this->websiteFactory->create();
        $this->websiteResource->load($website, $data->getCode(), 'code');
        if ($website->getId()) {
            $this->websiteResource->delete($website);
        }
        $this->storeManager->reinitStores();
    }

    /**
     * Prepare website data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = array_merge(self::DEFAULT_DATA, $data);

        return $this->dataProcessor->process($this, $data);
    }
}

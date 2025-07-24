<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate;

class TablerateFixture implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'website_id' => 1,
        'dest_country_id' => 'US',
        'dest_region_id' => 0,
        'dest_zip' => '*',
        'condition_name' => 'package_qty',
        'condition_value' => 1,
        'price' => 10,
        'cost' => 0
    ];

    /**
     * @var AdapterInterface $connection
     */
    private AdapterInterface $connection;

    /**
     * @var ObjectManagerInterface $objectManager
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @param ServiceFactory $serviceFactory
     * @param DataMerger $dataMerger
     */
    public function __construct(
        private ServiceFactory $serviceFactory,
        private DataMerger $dataMerger,
    ) {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->connection = $this->objectManager->get(ResourceConnection::class)->getConnection();
    }

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $resourceModel = $this->objectManager->create(Tablerate::class);
        $data = $this->dataMerger->merge($this::DEFAULT_DATA, $data);
        $columns = [
            'website_id',
            'dest_country_id',
            'dest_region_id',
            'dest_zip',
            'condition_name',
            'condition_value',
            'price',
            'cost'
        ];
        $resourceModel->getConnection()->insertArray(
            $resourceModel->getMainTable(),
            $columns,
            [
                $data
            ]
        );
        return new DataObject($data);
    }

    /**
     * @inheritDoc
     */
    public function revert(DataObject $data): void
    {
        $resourceModel = $this->objectManager->create(Tablerate::class);
        $this->connection->query("DELETE FROM {$resourceModel->getTable('shipping_tablerate')};");
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class SourceItem implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'sku' => 'SKU-%uniqid%',
        'source_code' => 'Source%uniqid%',
        'quantity' => 5,
        'status' => SourceItemInterface::STATUS_IN_STOCK,
    ];

    /**
     * @var ServiceFactory
     */
    private ServiceFactory $serviceFactory;

    /**
     * @var ProcessorInterface
     */
    private ProcessorInterface $dataProcessor;

    /**
     * @var DataMerger
     */
    private DataMerger $dataMerger;

    /**
     * @param ServiceFactory $serviceFactory
     * @param ProcessorInterface $dataProcessor
     * @param DataMerger $dataMerger
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        ProcessorInterface $dataProcessor,
        DataMerger $dataMerger
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->dataProcessor = $dataProcessor;
        $this->dataMerger = $dataMerger;
    }

    /**
     * @param array $data
     * @return DataObject|null
     */
    public function apply(array $data = []): ?DataObject
    {
        $service = $this->serviceFactory->create(SourceItemsSaveInterface::class, 'execute');

        return $service->execute(['sourceItems' => [$this->prepareData($data)]]);
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $service = $this->serviceFactory->create(SourceItemsDeleteInterface::class, 'execute');
        $service->execute(['sourceItems' => [$this->prepareData($data->getData())]]);
    }

    /**
     * Prepare product data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = $this->dataMerger->merge(self::DEFAULT_DATA, $data, false);

        return $this->dataProcessor->process($this, $data);
    }
}

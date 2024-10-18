<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Fixture;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class Category implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'id' => null,
        'name' => 'Category%uniqid%',
        'parent_id' => 2,
        'is_active' => true,
        'position' => 1,
        'level' => 1,
        'path' => null,
        'include_in_menu' => true,
        'available_sort_by' => [],
        'custom_attributes' => [
            'default_sort_by' => 'position'
        ],
        'extension_attributes' => [],
        'created_at' => null,
        'updated_at' => null,
    ];

    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @var DataMerger
     */
    private $dataMerger;

    /**
     * @param ServiceFactory $serviceFactory
     * @param ProcessorInterface $dataProcessor
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
     * {@inheritdoc}
     * @param array $data Parameters. Same format as Category::DEFAULT_DATA. Custom attributes and extension attributes
     *  can be passed directly in the outer array instead of custom_attributes or extension_attributes.
     */
    public function apply(array $data = []): ?DataObject
    {
        $service = $this->serviceFactory->create(CategoryRepositoryInterface::class, 'save');

        return $service->execute(
            [
                'category' => $this->prepareData($data)
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $service = $this->serviceFactory->create(CategoryRepositoryInterface::class, 'deleteByIdentifier');
        $service->execute(
            [
                'categoryId' => $data->getId()
            ]
        );
    }

    /**
     * Prepare category data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = $this->dataMerger->merge(self::DEFAULT_DATA, $data);

        return $this->dataProcessor->process($this, $data);
    }
}

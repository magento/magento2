<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Fixture;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\Hydrator;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Data fixture for customer group
 */
class CustomerGroup implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        GroupInterface::CODE => 'Customergroup%uniqid%',
        GroupInterface::TAX_CLASS_ID => 3,
    ];

    /**
     * @var ServiceFactory
     */
    private ServiceFactory $serviceFactory;

    /**
     * @var Hydrator
     */
    private Hydrator $hydrator;

    /**
     * @var DataMerger
     */
    private DataMerger $dataMerger;

    /**
     * @var ProcessorInterface
     */
    private ProcessorInterface $dataProcessor;

    /**
     * @param ServiceFactory $serviceFactory
     * @param Hydrator $hydrator
     * @param DataMerger $dataMerger
     * @param ProcessorInterface $dataProcessor
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        Hydrator $hydrator,
        DataMerger $dataMerger,
        ProcessorInterface $dataProcessor
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->hydrator = $hydrator;
        $this->dataMerger = $dataMerger;
        $this->dataProcessor = $dataProcessor;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $customerGroup = $this->serviceFactory->create(GroupRepositoryInterface::class, 'save')->execute(
            [
                'group' => $this->dataProcessor->process($this, $this->dataMerger->merge(self::DEFAULT_DATA, $data))
            ]
        );

        return new DataObject($this->hydrator->extract($customerGroup));
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $this->serviceFactory->create(GroupRepositoryInterface::class, 'deleteById')->execute(
            [
                'id' => $data->getId()
            ]
        );
    }
}

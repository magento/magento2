<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class TaxClass implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'class_name' => 'taxclass%uniqid%',
        'class_type' => null,
    ];

    /**
     * @var ServiceFactory
     */
    private ServiceFactory $serviceFactory;

    /**
     * @var TaxClassRepositoryInterface
     */
    private TaxClassRepositoryInterface $taxClassRepository;

    /**
     * @var ProcessorInterface
     */
    private ProcessorInterface $dataProcessor;

    /**
     * @param ServiceFactory $serviceFactory
     * @param TaxClassRepositoryInterface $taxClassRepository
     * @param ProcessorInterface $dataProcessor
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        TaxClassRepositoryInterface $taxClassRepository,
        ProcessorInterface $dataProcessor
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->taxClassRepository = $taxClassRepository;
        $this->dataProcessor = $dataProcessor;
    }

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $service = $this->serviceFactory->create(TaxClassRepositoryInterface::class, 'save');
        $taxClassId = $service->execute(
            [
                'taxClass' => $this->dataProcessor->process($this, array_merge(self::DEFAULT_DATA, $data))
            ]
        );

        return $this->taxClassRepository->get($taxClassId);
    }

    /**
     * @inheritDoc
     */
    public function revert(DataObject $data): void
    {
        $service = $this->serviceFactory->create(TaxClassRepositoryInterface::class, 'deleteById');
        $service->execute(['taxClassId' => $data->getId()]);
    }
}

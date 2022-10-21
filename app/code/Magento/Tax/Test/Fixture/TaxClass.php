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
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class TaxClass implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'class_name' => '%uniqid%',
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
     * @param ServiceFactory $serviceFactory
     * @param TaxClassRepositoryInterface $taxClassRepository
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        TaxClassRepositoryInterface $taxClassRepository
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->taxClassRepository = $taxClassRepository;
    }

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $service = $this->serviceFactory->create(TaxClassRepositoryInterface::class, 'save');
        $taxClassId = $service->execute(['taxClass' => array_merge(self::DEFAULT_DATA, $data)]);

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

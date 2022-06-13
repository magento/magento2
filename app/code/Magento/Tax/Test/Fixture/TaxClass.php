<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class TaxClass implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'class_name' => '%uniqid%',
        'class_type' => '%uniqid%',
    ];

    /**
     * @var ServiceFactory
     */
    private ServiceFactory $serviceFactory;

    /**
     * @var DataObjectFactory
     */
    private DataObjectFactory $dataObjectFactory;

    /**
     * @param ServiceFactory $serviceFactory
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(ServiceFactory $serviceFactory, DataObjectFactory $dataObjectFactory)
    {
        $this->serviceFactory = $serviceFactory;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $service = $this->serviceFactory->create(TaxClassRepositoryInterface::class, 'save');

        return $this->dataObjectFactory->create()->addData([
            'id' => $service->execute(['taxClass' => array_merge(self::DEFAULT_DATA, $data)]),
        ]);
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

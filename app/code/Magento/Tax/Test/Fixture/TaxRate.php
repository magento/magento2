<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class TaxRate implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'code' => '%uniqid%',
        'rate' => '%uniqid%',
        'tax_country_id' => '%uniqid%',
    ];

    /**
     * @var ServiceFactory
     */
    private ServiceFactory $serviceFactory;

    /**
     * @param ServiceFactory $serviceFactory
     */
    public function __construct(ServiceFactory $serviceFactory)
    {
        $this->serviceFactory = $serviceFactory;
    }

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $service = $this->serviceFactory->create(TaxRateRepositoryInterface::class, 'save');

        return $service->execute([
            'taxRate' => array_merge(self::DEFAULT_DATA, $data),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function revert(DataObject $data): void
    {
        $service = $this->serviceFactory->create(TaxRateRepositoryInterface::class, 'deleteById');
        $service->execute(['rateId' => $data->getId()]);
    }
}

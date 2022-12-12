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
        'code' => 'taxrate%uniqid%',
        'rate' => 10,
        'tax_country_id' => 'US',
        'tax_region_id' => 0,
        'region_name' => null,
        'tax_postcode' => '*',
        'zip_is_range' => null,
        'zip_from' => null,
        'zip_to' => null,
        'titles' => [],
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

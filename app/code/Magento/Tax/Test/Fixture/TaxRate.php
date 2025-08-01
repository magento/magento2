<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\TestFramework\Fixture\Api\DataMerger;
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
        'titles' => []
    ];

    /**
     * TaxRate Constructor
     *
     * @param ServiceFactory $serviceFactory
     * @param DataMerger $dataMerger
     */
    public function __construct(
        private readonly ServiceFactory $serviceFactory,
        private readonly DataMerger $dataMerger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $service = $this->serviceFactory->create(TaxRateRepositoryInterface::class, 'save');
        $data = $this->dataMerger->merge(
            self::DEFAULT_DATA,
            $data
        );
        $data['code'] = str_replace('%uniqid%', uniqid(), $data['code']);

        return $service->execute(['taxRate' => $data]);
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

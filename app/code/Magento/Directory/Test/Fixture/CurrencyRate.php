<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Fixture;

use Magento\Directory\Model\Currency;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\FormatInterface;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Data fixture for currency rate.
 */
class CurrencyRate implements RevertibleDataFixtureInterface
{
    /**
     * @var FormatInterface
     */
    private $format;

    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        FormatInterface $format,
        ServiceFactory $serviceFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->format = $format;
        $this->serviceFactory = $serviceFactory;
        $this->resourceConnection = $resourceConnection;
    }

    public function apply(array $data = []): ?DataObject
    {
        if (is_array($data)) {
            foreach ($data as $currencyCode => $rate) {
                foreach ($rate as $currencyTo => $value) {
                    $value = abs((float) $this->format->getNumber($value));
                    $data[$currencyCode][$currencyTo] = $value;
                }
            }
            $service = $this->serviceFactory->create(Currency::class, 'saveRates');

            return $service->execute(['rates' => $data]);
        }
    }

    public function revert(DataObject $data): void
    {
        $connection = $this->resourceConnection->getConnection();
        $currencyTable = $this->resourceConnection->getTableName('directory_currency_rate');

        foreach ($data as $currencyCode => $rate) {
            foreach ($rate as $currencyTo) {
                $connection->delete(
                    $currencyTable,
                    [
                        'currency_from = ?' => $currencyCode,
                        'currency_to = ?' => $currencyTo,
                    ]
                );
            }
        }
    }
}

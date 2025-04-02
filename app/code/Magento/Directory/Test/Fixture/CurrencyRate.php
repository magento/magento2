<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Fixture;

use Magento\Directory\Model\Currency;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\FormatInterface;
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
     * @var Currency
     */
    private $currency;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        FormatInterface $format,
        Currency $currency,
        ResourceConnection $resourceConnection
    ) {
        $this->format = $format;
        $this->currency = $currency;
        $this->resourceConnection = $resourceConnection;
    }

    public function apply(array $data = []): ?DataObject
    {
        foreach ($data as $currencyCode => $rate) {
            foreach ($rate as $currencyTo => $value) {
                $value = abs((float) $this->format->getNumber($value));
                $data[$currencyCode][$currencyTo] = $value;
            }
        }

        return $this->currency->saveRates($data);
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

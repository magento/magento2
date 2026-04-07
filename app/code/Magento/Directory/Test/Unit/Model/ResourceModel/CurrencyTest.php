<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model\ResourceModel;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Directory\Model\ResourceModel\Currency;
use Magento\Framework\App\ResourceConnection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CurrencyTest extends TestCase
{
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * Ensures that after setting new rates, the getRate method returns the updated rates and does not return cached rates. 
     */
    public function testGetRateAfterSettingNewRates()
    {
        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();

        $v0_usdToGbpRate = 0.5;
        $v1_usdToGbpRate = 1.0;
        $v1_gbpToUsdRate = 75.0;
        $rateTable = [
            0 => [
                'USD' => [
                    'GBP' => $v0_usdToGbpRate,
                ]
            ],
            1 => [
                'USD' => [
                    'GBP' => $v1_usdToGbpRate,
                ],
                'GBP' => [
                    'USD' => $v1_gbpToUsdRate,
                ],
            ],
        ];
        $ratesVersion = 0;

        $connection = $this->createMock(Mysql::class);
        $connection->method('select')->willReturn($select);
        $connection->expects($this->exactly(4))->method('fetchOne')->willReturnCallback(function ($query, $bind) use (&$rateTable, &$ratesVersion) {
            $currencyFrom = $bind[':currency_from'];
            $currencyTo = $bind[':currency_to'];
            return $rateTable[$ratesVersion][$currencyFrom][$currencyTo] ?? null;
        });

        $resourceConnection = $this->createMock(ResourceConnection::class);
        $resourceConnection->method('getConnection')->willReturn($connection);

        $context = $this->createMock(Context::class);
        $context->method('getResources')->willReturn($resourceConnection);

        $model = $this->objectManager->getObject(Currency::class, [
            'context' => $context,
        ]);

        // set rates [USD => [GBP => 0.5]]
        $model->saveRates($rateTable[$ratesVersion]);

        // get the rate for USD => GBP and assert it is 0.5
        $this->assertEquals($v0_usdToGbpRate, $model->getRate('USD', 'GBP'));

        // get the rate for GBP => USD and assert it is null
        $this->assertNull($model->getRate('GBP', 'USD'));

        // set rates [USD => [GBP => 1.0], GBP => [USD => 75.0]]
        // mimic rates being updated in the database by incrementing the rates version
        $ratesVersion++;
        $model->saveRates($rateTable[$ratesVersion]);

        // get the rate for USD => GBP and assert it is 1.0
        $this->assertEquals($v1_usdToGbpRate, $model->getRate('USD', 'GBP'));

        // get the rate for GBP => USD and assert it is 75.0
        $this->assertEquals($v1_gbpToUsdRate, $model->getRate('GBP', 'USD'));
    }
}

<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GridPaymentMethodColumnTest extends TestCase
{
    #[DataProvider('gridTableProvider')]
    public function testPaymentMethodColumnFitsOrderPaymentMethod(string $gridTable): void
    {
        $resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $connection = $resource->getConnection('sales');

        $source = $connection->describeTable($resource->getTableName('sales_order_payment', 'sales'))['method'];
        $grid = $connection->describeTable($resource->getTableName($gridTable, 'sales'))['payment_method'];

        $this->assertGreaterThanOrEqual((int)$source['LENGTH'], (int)$grid['LENGTH']);
    }

    public static function gridTableProvider(): array
    {
        return [
            ['sales_order_grid'],
            ['sales_invoice_grid'],
            ['sales_shipment_grid'],
            ['sales_creditmemo_grid'],
        ];
    }
}

<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests for stock alert grid controller
 *
 * @see \Magento\Catalog\Controller\Adminhtml\Product\AlertsStockGrid
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 */
class AlertsStockGridTest extends AbstractAlertTest
{
    /**
     * @magentoDataFixture Magento/ProductAlert/_files/simple_product_with_two_alerts.php
     *
     * @param string $email
     * @param int|null $limit
     * @param int $expectedCount
     * @return void
     */
    #[DataProvider('stockLimitProvider')]
    public function testExecute(string $email, ?int $limit, int $expectedCount): void
    {
        $this->prepareRequest('simple', 'default', $limit);
        $this->dispatch('backend/catalog/product/alertsStockGrid');
        $this->assertGridRecords($email, $expectedCount);
    }

    /**
     * @return array
     */
    public static function stockLimitProvider(): array
    {
        return [
            'default_limit' => [
                'email' => 'customer@example.com',
                'limit' => null,
                'expectedCount' => 2,
            ],
            'limit_1' => [
                'email' => 'customer@example.com',
                'limit' => 1,
                'expectedCount' => 1,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getRecordXpathTemplate(): string
    {
        return "//div[@id='alertStock']//tbody/tr/td[contains(text(), '%s')]";
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product;

/**
 * Tests for price alert grid controller
 *
 * @see \Magento\Catalog\Controller\Adminhtml\Product\AlertsPriceGrid
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 */
class AlertsPriceGridTest extends AbstractAlertTest
{
    /**
     * @dataProvider priceLimitProvider
     *
     * @magentoDataFixture Magento/ProductAlert/_files/simple_product_with_two_alerts.php
     *
     * @param string $email
     * @param int|null $limit
     * @param $expectedCount
     * @return void
     */
    public function testExecute(string $email, ?int $limit, $expectedCount): void
    {
        $this->prepareRequest('simple', 'default', $limit);
        $this->dispatch('backend/catalog/product/alertsPriceGrid');
        $this->assertGridRecords($email, $expectedCount);
    }

    /**
     * @return array
     */
    public function priceLimitProvider(): array
    {
        return [
            'default_limit' => [
                'email' => 'customer@example.com',
                'limit' => null,
                'expected_count' => 2,
            ],
            'limit_1' => [
                'email' => 'customer@example.com',
                'limit' => 1,
                'expected_count' => 1,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getRecordXpathTemplate(): string
    {
        return "//div[@id='alertPrice']//tbody/tr/td[contains(text(), '%s')]";
    }
}

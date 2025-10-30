<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts;

use Magento\Framework\View\LayoutInterface;

/**
 * Check price alert grid
 *
 * @see \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts\Price
 *
 * @magentoAppArea adminhtml
 */
class PriceTest extends AbstractAlertTest
{
    /** @var Price */
    private $block;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Price::class);
    }

    /**
     * @dataProvider alertsDataProvider
     *
     * @magentoDbIsolation disabled
     *
     * @magentoDataFixture Magento/ProductAlert/_files/product_alert.php
     * @magentoDataFixture Magento/ProductAlert/_files/price_alert_on_second_website.php
     *
     * @param string $sku
     * @param string $expectedEmail
     * @param string|null $storeCode
     * @return void
     */
    public function testGridCollectionWithStoreId(string $sku, string $expectedEmail, ?string $storeCode = null): void
    {
        $this->prepareRequest($sku, $storeCode);
        $collection = $this->block->getPreparedCollection();
        $this->assertCount(1, $collection);
        $this->assertEquals($expectedEmail, $collection->getFirstItem()->getEmail());
    }

    /**
     * @return array
     */
    public static function alertsDataProvider(): array
    {
        return [
            'without_store_id_filter' => [
                'sku' => 'simple',
                'expectedEmail' => 'customer@example.com',
            ],
            'with_store_id_filter' => [
                'sku' => 'simple_on_second_website_for_price_alert',
                'expectedEmail' => 'customer_second_ws_with_addr@example.com',
                'storeCode' => 'fixture_third_store',
            ],
        ];
    }

    /**
     * @dataProvider storeProvider
     *
     * @param string|null $storeCode
     * @return void
     */
    public function testGetGridUrl(?string $storeCode): void
    {
        $this->prepareRequest(null, $storeCode);
        $this->assertGridUrl($this->block->getGridUrl(), $storeCode);
    }

    /**
     * @return array
     */
    public static function storeProvider(): array
    {
        return [
            'without_store_id_param' => [
                'storeCode' => null,
            ],
            'with_store_id_param' => [
                'storeCode' => 'default',
            ],
        ];
    }
}

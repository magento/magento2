<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\Component\Listing\Columns;

use Magento\Catalog\Ui\DataProvider\Product\Modifier\PriceAttributes;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[AppArea('adminhtml')]
class PriceTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeId = (int)$this->objectManager->get(StoreManagerInterface::class)->getStore('default')->getId();
    }

    #[
        Config('currency/options/base', 'USD', 'store', 'default'),
        Config('currency/options/default', 'CNY', 'store', 'default'),
        Config('currency/options/allow', 'CNY,USD', 'store', 'default'),
        DataFixture('Magento/Directory/_files/usd_cny_rate.php'),
    ]
    public function testPriceColumnUsesBaseCurrencyOfFilteredStore(): void
    {
        $context = $this->createStub(ContextInterface::class);
        $context->method('getFilterParam')->willReturn($this->storeId);
        $column = $this->objectManager->create(
            Price::class,
            ['context' => $context, 'data' => ['name' => 'price']]
        );

        $dataSource = $column->prepareDataSource(['data' => ['items' => [['price' => '10.000000']]]]);

        $this->assertSame('$10.00', $dataSource['data']['items'][0]['price']);
    }

    #[
        Config('currency/options/base', 'USD', 'store', 'default'),
        Config('currency/options/default', 'CNY', 'store', 'default'),
        Config('currency/options/allow', 'CNY,USD', 'store', 'default'),
        DataFixture('Magento/Directory/_files/usd_cny_rate.php'),
    ]
    public function testPriceAttributesUseBaseCurrencyOfItemStore(): void
    {
        $modifier = $this->objectManager->create(
            PriceAttributes::class,
            ['priceAttributeList' => ['special_price']]
        );

        $data = $modifier->modifyData(
            ['items' => [['type_id' => 'simple', 'store_id' => $this->storeId, 'special_price' => '5.000000']]]
        );

        $this->assertSame('$5.00', $data['items'][0]['special_price']);
    }
}

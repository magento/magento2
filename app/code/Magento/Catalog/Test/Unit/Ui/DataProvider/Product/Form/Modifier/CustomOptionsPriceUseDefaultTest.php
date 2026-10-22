<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Config\Source\Product\Options\Price as ProductOptionsPrice;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomOptionsPriceUseDefaultTest extends TestCase
{
    #[DataProvider('priceUseDefaultProvider')]
    public function testPriceFieldsUseDefaultServiceOnStoreView(int $storeId, bool $priceGlobal, bool $expected): void
    {
        $model = $this->createModel($storeId, $priceGlobal);

        $staticType = (new \ReflectionMethod($model, 'getStaticTypeContainerConfig'))->invoke($model, 10);
        $selectType = (new \ReflectionMethod($model, 'getSelectTypeGridConfig'))->invoke($model, 10);
        $staticPrice = $staticType['children'][CustomOptions::FIELD_PRICE_NAME]['arguments']['data']['config'];
        $selectPrice = $selectType['children']['record']['children'][CustomOptions::FIELD_PRICE_NAME]
            ['arguments']['data']['config'];

        $this->assertSame($expected, isset($staticPrice['service']));
        $this->assertSame($expected, isset($selectPrice['service']));
        if ($expected) {
            $this->assertSame(
                'Magento_Catalog/form/element/helper/custom-option-service',
                $staticPrice['service']['template']
            );
            $this->assertSame(
                'Magento_Catalog/form/element/helper/custom-option-type-service',
                $selectPrice['service']['template']
            );
            $this->assertStringEndsWith('.is_use_default_price', $staticPrice['imports']['isUseDefault']);
            $this->assertArrayHasKey('optionTypeId', $selectPrice['imports']);
        }
    }

    public static function priceUseDefaultProvider(): array
    {
        return [
            'store view, website price scope' => [1, false, true],
            'store view, global price scope' => [1, true, false],
            'default scope' => [0, false, false],
        ];
    }

    private function createModel(int $storeId, bool $priceGlobal): CustomOptions
    {
        $product = $this->createMock(Product::class);
        $product->method('getStoreId')->willReturn($storeId);
        $locator = $this->createMock(LocatorInterface::class);
        $locator->method('getProduct')->willReturn($product);
        $catalogHelper = $this->createMock(CatalogHelper::class);
        $catalogHelper->method('isPriceGlobal')->willReturn($priceGlobal);
        $productOptionsPrice = $this->createMock(ProductOptionsPrice::class);
        $productOptionsPrice->method('prefixesToOptionArray')->willReturn([]);
        $productOptionsPrice->method('toOptionArray')->willReturn([]);
        $currency = $this->createMock(PriceCurrencyInterface::class);
        $store = $this->createPartialMock(Store::class, ['getBaseCurrency']);
        $store->method('getBaseCurrency')->willReturn($currency);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        return new CustomOptions(
            $locator,
            $storeManager,
            $this->createMock(ConfigInterface::class),
            $productOptionsPrice,
            $this->createMock(UrlInterface::class),
            new ArrayManager(),
            $catalogHelper
        );
    }
}

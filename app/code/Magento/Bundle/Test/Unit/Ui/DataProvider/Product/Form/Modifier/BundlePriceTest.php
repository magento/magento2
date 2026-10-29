<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePrice;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\Stdlib\ArrayManager;

class BundlePriceTest extends AbstractModifierTestCase
{
    /**
     * @return BundlePrice
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(
            BundlePrice::class,
            [
                'locator' => $this->locatorMock,
                'arrayManager' => $this->arrayManagerMock
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testModifyMeta()
    {
        $this->productMock->setId(true);
        $this->productMock->setPriceType(0);
        $priceTypePath = 'bundle-items/children/' . BundlePrice::CODE_PRICE_TYPE;
        $priceTypeConfigPath = $priceTypePath . BundlePrice::META_CONFIG_PATH;
        $pricePath = 'product-details/children/' . ProductAttributeInterface::CODE_PRICE;
        $priceConfigPath = $pricePath . BundlePrice::META_CONFIG_PATH;
        $sourceMeta = [
            'bundle-items' => [
                'children' => [
                    BundlePrice::CODE_PRICE_TYPE => []
                ]
            ]
        ];
        $priceTypeParams = [
            'disabled' => true,
            'valueMap' => [
                'false' => '1',
                'true' => '0'
            ],
            'validation' => [
                'required-entry' => false
            ]
        ];
        $priceTypeMeta = [
            'bundle-items' => [
                'children' => [
                    BundlePrice::CODE_PRICE_TYPE => $priceTypeParams
                ]
            ]
        ];
        $priceParams = [
            'imports' => [
                'disabled' => 'ns = ${ $.ns }, index = ' . BundlePrice::CODE_PRICE_TYPE . ':checked',
                '__disableTmpl' => ['disabled' => false],
            ]
        ];
        $priceMeta = [
            'product-details' => [
                'children' => [
                    BundlePrice::CODE_PRICE_TYPE => []
                ]
            ],
            'bundle-items' => [
                'children' => [
                    ProductAttributeInterface::CODE_PRICE => $priceParams
                ]
            ]
        ];
        $taxParams = [
            'service' => [
                'template' => ''
            ]
        ];

        $this->arrayManagerMock->expects($this->any())
            ->method('findPath')
            ->willReturnCallback(
                function (
                    $code,
                    $data,
                    $path = null
                ) use (
                    $sourceMeta,
                    $priceTypeMeta,
                    $priceTypePath,
                    $pricePath
                ) {
                    if ($code === BundlePrice::CODE_PRICE_TYPE && $data === $sourceMeta) {
                        return $priceTypePath;
                    }
                    if ($code === ProductAttributeInterface::CODE_PRICE && $data === $priceTypeMeta) {
                        return $pricePath;
                    }
                    if ($code === BundlePrice::CODE_TAX_CLASS_ID) {
                        return $pricePath;
                    }
                    return null;
                }
            );
        $this->arrayManagerMock->expects($this->exactly(4))
            ->method('merge')
            ->willReturnCallback(
                function (
                    $path,
                    $data,
                    $params
                ) use (
                    $sourceMeta,
                    $priceTypeMeta,
                    $priceMeta,
                    $priceTypeConfigPath,
                    $priceConfigPath,
                    $priceTypeParams,
                    $priceParams
                ) {
                    if ($path === $priceTypeConfigPath && $data === $sourceMeta && $params === $priceTypeParams) {
                        return $priceTypeMeta;
                    }
                    if ($path === $priceConfigPath && $data === $priceTypeMeta && $params === $priceParams) {
                        return $priceMeta;
                    }
                    if ($path === $priceConfigPath && $data === $priceMeta) {
                        return $priceMeta;
                    }
                    return $priceMeta;
                }
            );

        $this->assertSame($priceMeta, $this->getModel()->modifyMeta($sourceMeta));
    }

    public function testModifyData()
    {
        $expectedData = [];
        $this->assertEquals($expectedData, $this->getModel()->modifyData($expectedData));
    }
}

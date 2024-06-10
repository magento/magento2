<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundleSku;
use Magento\Framework\Stdlib\ArrayManager;

class BundleSkuTest extends AbstractModifierTestCase
{
    /**
     * @return BundleSku
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(
            BundleSku::class,
            ['arrayManager' => $this->arrayManagerMock]
        );
    }

    public function testModifyMeta()
    {
        $skuTypePath = 'bundle-items/children/' . BundleSku::CODE_SKU_TYPE;
        $skuTypeConfigPath = $skuTypePath . BundleSku::META_CONFIG_PATH;
        $sourceMeta = [
            'bundle-items' => [
                'children' => [
                    BundleSku::CODE_SKU_TYPE => []
                ]
            ]
        ];
        $skuTypeParams = [
            'valueMap' => [
                'false' => '1',
                'true' => '0'
            ],
            'validation' => [
                'required-entry' => false
            ]
        ];
        $skuTypeMeta = [
            'bundle-items' => [
                'children' => [
                    BundleSku::CODE_SKU_TYPE => $skuTypeParams
                ]
            ]
        ];

        $this->arrayManagerMock->expects(static::any())
            ->method('findPath')
            ->willReturnMap(
                [
                    [
                        BundleSku::CODE_SKU_TYPE,
                        $sourceMeta,
                        null,
                        'children',
                        ArrayManager::DEFAULT_PATH_DELIMITER,
                        $skuTypePath
                    ]
                ]
            );
        $this->arrayManagerMock->expects($this->once())
            ->method('merge')
            ->with($skuTypeConfigPath, $sourceMeta, $skuTypeParams)
            ->willReturn($skuTypeMeta);

        $this->assertSame($skuTypeMeta, $this->getModel()->modifyMeta($sourceMeta));
    }

    public function testModifyData()
    {
        $expectedData = [];
        $this->assertEquals($expectedData, $this->getModel()->modifyData($expectedData));
    }
}

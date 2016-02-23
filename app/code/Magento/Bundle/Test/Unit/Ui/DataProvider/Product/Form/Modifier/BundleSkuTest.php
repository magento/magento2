<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundleSku;

class BundleSkuTest extends AbstractModifierTest
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
        $skuTypePath = 'bundle-items/children/' . BundleSku::CODE_SKU_TYPE . BundleSku::META_CONFIG_PATH;
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

        $this->arrayManagerMock->expects($this->once())
            ->method('merge')
            ->with($skuTypePath, $sourceMeta, $skuTypeParams)
            ->willReturn($skuTypeMeta);

        $this->assertSame($skuTypeMeta, $this->getModel()->modifyMeta($sourceMeta));
    }

    public function testModifyData()
    {
        $expectedData = [];
        $this->assertEquals($expectedData, $this->getModel()->modifyData($expectedData));
    }
}

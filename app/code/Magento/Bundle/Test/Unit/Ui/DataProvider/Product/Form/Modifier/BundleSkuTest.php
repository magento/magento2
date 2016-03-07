<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundleSku;
use Magento\Catalog\Model\AttributeConstantsInterface;

/**
 * Class BundleSkuTest
 */
class BundleSkuTest extends AbstractModifierTest
{
    /**
     * @return BundleSku
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(BundleSku::class, [
            'arrayManager' => $this->arrayManagerMock,
        ]);
    }

    /**
     * @return void
     */
    public function testModifyMeta()
    {
        $sourceMeta = [
            'testGroup' => [
                'children' => [
                    AttributeConstantsInterface::CODE_SKU => [
                        'componentType' => 'testComponent',
                    ],
                ]
            ],
        ];
        $modifiedMeta = $this->getModel()->modifyMeta($sourceMeta);
        $this->assertArrayHasKey(BundleSku::CODE_SKU_TYPE, $modifiedMeta['testGroup']['children']);
    }

    /**
     * @return void
     */
    public function testModifyData()
    {
        $expectedData = [];
        $this->assertEquals($expectedData, $this->getModel()->modifyData($expectedData));
    }
}

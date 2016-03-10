<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundleWeight;

/**
 * Class BundleWeightTest
 */
class BundleWeightTest extends AbstractModifierTest
{
    /**
     * @return BundleWeight
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(BundleWeight::class, [
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
                    BundleWeight::CODE_CONTAINER_WEIGHT  => [
                        'componentType' => 'testComponent',
                    ],
                ]
            ],
        ];
        $modifiedMeta = $this->getModel()->modifyMeta($sourceMeta);
        $this->assertArrayHasKey(BundleWeight::CODE_WEIGHT_TYPE, $modifiedMeta['testGroup']['children']);
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

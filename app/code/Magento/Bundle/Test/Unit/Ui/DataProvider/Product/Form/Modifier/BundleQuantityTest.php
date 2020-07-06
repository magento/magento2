<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundleQuantity;

class BundleQuantityTest extends AbstractModifierTest
{
    /**
     * @return BundleQuantity
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(BundleQuantity::class);
    }

    /**
     * @return void
     */
    public function testModifyMeta()
    {
        $sourceMeta = [
            'testGroup' => [
                'children' => [
                    BundleQuantity::CODE_QTY_CONTAINER  => [
                        'componentType' => 'testComponent',
                    ],
                ]
            ],
        ];
        $modifiedMeta = $this->getModel()->modifyMeta($sourceMeta);
        $this->assertArrayHasKey(
            BundleQuantity::CODE_QUANTITY,
            $modifiedMeta['testGroup']['children'][BundleQuantity::CODE_QTY_CONTAINER]['children']
        );
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

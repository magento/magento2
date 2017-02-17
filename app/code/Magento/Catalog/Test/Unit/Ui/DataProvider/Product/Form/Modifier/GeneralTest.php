<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\General;

/**
 * Class GeneralTest
 *
 * @method General getModel
 */
class GeneralTest extends AbstractModifierTest
{
    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(General::class, [
            'locator' => $this->locatorMock,
            'arrayManager' => $this->arrayManagerMock,
        ]);
    }

    public function testModifyMeta()
    {
        $this->assertNotEmpty($this->getModel()->modifyMeta([
            'first_panel_code' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => 'Test label',
                        ]
                    ],
                ]
            ]
        ]));
    }
}

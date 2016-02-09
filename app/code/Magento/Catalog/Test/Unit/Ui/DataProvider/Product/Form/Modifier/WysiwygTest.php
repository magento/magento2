<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\AttributeConstantsInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Wysiwyg;

/**
 * Class WysiwygTest
 *
 * @method Wysiwyg getModel
 */
class WysiwygTest extends AbstractModifierTest
{
    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(Wysiwyg::class, [
            'arrayManager' => $this->arrayManagerMock,
        ]);
    }

    public function testModifyMeta()
    {
        $groupCode = 'test_group';
        $expectedMeta = [
            $groupCode =>
                [
                    'children' => [
                        'container_' . AttributeConstantsInterface::CODE_DESCRIPTION => [
                            'children' => [
                            ]
                        ],
                        'container_' . AttributeConstantsInterface::CODE_SHORT_DESCRIPTION => [
                            'children' => [
                            ]
                        ],
                    ],
                ],
        ];

        $this->assertSame($expectedMeta, $this->getModel()->modifyMeta([
            'test_group' => [
                'children' => [
                    'container_' . AttributeConstantsInterface::CODE_DESCRIPTION => [
                        'children' => []
                    ],
                    'container_' . AttributeConstantsInterface::CODE_SHORT_DESCRIPTION => [
                        'children' => []
                    ],
                ]
            ],
        ]));
    }

    public function testModifyData()
    {
        $this->assertSame($this->getSampleData(), $this->getModel()->modifyData($this->getSampleData()));
    }
}

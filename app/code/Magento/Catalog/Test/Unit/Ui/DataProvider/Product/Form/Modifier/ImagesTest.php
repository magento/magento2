<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Product\Type;

/**
 * @method \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Images getModel
 */
class ImagesTest extends AbstractModifierTest
{
    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(\Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Images::class, [
            'locator' => $this->locatorMock,
        ]);
    }

    public function testModifyData()
    {
        $this->productMock->expects($this->once())->method('getId')->willReturn(2051);
        $actualResult = $this->getModel()->modifyData($this->getSampleData());
        $this->assertSame("", $actualResult[2051]['product']['media_gallery']['images'][0]['label']);
    }

    public function testModifyMeta()
    {
        $meta = [
            \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Images::CODE_IMAGE_MANAGEMENT_GROUP => [
                'children' => [],
                'label' => __('Images'),
                'sortOrder' => '20',
                'componentType' => 'fieldset'
            ]
        ];

        $this->assertSame([], $this->getModel()->modifyMeta($meta));
    }

    /**
     * {@inheritdoc}
     */
    protected function getSampleData()
    {
        return [
            2051 => [
                'product' => [
                    'media_gallery' => [
                        'images' => [
                            [
                                'label' => null
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}

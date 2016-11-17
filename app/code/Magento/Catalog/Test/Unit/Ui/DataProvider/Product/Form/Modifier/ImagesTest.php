<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Images;

/**
 * @method Images getModel
 */
class ImagesTest extends AbstractModifierTest
{
    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        $this->productMock->expects($this->once())->method('getId')->willReturn(2051);
        $actualResult = $this->getModel()->modifyData($this->getSampleData());
        $this->assertSame('', $actualResult[2051]['product']['media_gallery']['images'][0]['label']);
    }

    public function testModifyData()
    {
        $this->assertSame($this->getSampleData(), $this->getModel()->modifyData($this->getSampleData()));
    }

    public function testModifyMeta()
    {
        $meta = [
            Images::CODE_IMAGE_MANAGEMENT_GROUP => [
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

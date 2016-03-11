<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\General;

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
        $productId = 1;
        $modelId = 1;

        $data = [
            $modelId => [
                \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\General::DATA_SOURCE_DEFAULT => [
                    ProductAttributeInterface::CODE_SKU => 'product_42',
                    ProductAttributeInterface::CODE_PRICE => '42.00',
                    ProductAttributeInterface::CODE_STATUS => '1',
                    \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Images::CODE_MEDIA_GALLERY => [
                        'images' => [
                            [
                                'value_id' => '1',
                                'file' => 'filename.jpg',
                                'media_type' => 'image',
                            ]
                        ]
                    ],
                    \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Images::CODE_IMAGE => 'filename.jpg',
                    \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Images::CODE_SMALL_IMAGE => 'filename.jpg',
                    \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Images::CODE_THUMBNAIL => 'filename.jpg',
                    \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Images::CODE_SWATCH_IMAGE => 'filename.jpg',
                ]
            ]
        ];

        $expectedData = [
            $modelId => [
                General::DATA_SOURCE_DEFAULT => [
                    ProductAttributeInterface::CODE_SKU => 'product_42',
                    ProductAttributeInterface::CODE_PRICE => '42.00',
                    ProductAttributeInterface::CODE_STATUS => '1',
                ]
            ]
        ];

        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);

        $this->assertSame($expectedData, $this->getModel()->modifyData($data));
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
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\ConfigurableProduct\Test\Unit\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Helper\Image|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_imageHelperMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productMock;

    protected function setUp()
    {
        $this->_imageHelperMock = $this->getMock('Magento\Catalog\Helper\Image', [], [], '', false);
        $this->_productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);

        $this->_model = new \Magento\ConfigurableProduct\Helper\Data($this->_imageHelperMock);
    }

    public function testGetAllowAttributes()
    {
        $typeInstanceMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable', [], [], '', false
        );
        $typeInstanceMock->expects($this->once())
            ->method('getConfigurableAttributes')
            ->with($this->_productMock);

        $this->_productMock->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstanceMock));

        $this->_model->getAllowAttributes($this->_productMock);
    }

    /**
     * @param array $expected
     * @param array $data
     * @dataProvider getOptionsDataProvider
     */
    public function testGetOptions(array $expected, array $data)
    {
        if (count($data['allowed_products'])) {
            $imageHelper1 = $this->getMockBuilder('Magento\Catalog\Helper\Image')
                ->disableOriginalConstructor()
                ->getMock();
            $imageHelper1->expects($this->any())
                ->method('getUrl')
                ->willReturn('http://example.com/base_img_url');

            $imageHelper2 = $this->getMockBuilder('Magento\Catalog\Helper\Image')
                ->disableOriginalConstructor()
                ->getMock();
            $imageHelper2->expects($this->any())
                ->method('getUrl')
                ->willReturn('http://example.com/base_img_url_2');

            $this->_imageHelperMock->expects($this->any())
                ->method('init')
                ->willReturnMap([
                    [$data['current_product_mock'], 'product_page_image_large', [], $imageHelper1],
                    [$data['allowed_products'][0], 'product_page_image_large', [], $imageHelper1],
                    [$data['allowed_products'][1], 'product_page_image_large', [], $imageHelper2],
                ]);
        }

        $this->assertEquals(
            $expected,
            $this->_model->getOptions($data['current_product_mock'], $data['allowed_products'])
        );
    }

    /**
     * @return array
     */
    public function getOptionsDataProvider()
    {
        $currentProductMock = $this->getMock(
            'Magento\Catalog\Model\Product', ['getTypeInstance', '__wakeup'], [], '', false
        );
        $provider = [];
        $provider[] = [
            [],
            [
                'allowed_products' => [],
                'current_product_mock' => $currentProductMock,
            ],
        ];

        $attributesCount = 3;
        $attributes = [];
        for ($i = 1; $i < $attributesCount; $i++) {
            $attribute = $this->getMock(
                'Magento\Framework\DataObject', ['getProductAttribute'], [], '', false
            );
            $productAttribute = $this->getMock(
                'Magento\Framework\DataObject',
                ['getId', 'getAttributeCode'],
                [],
                '',
                false
            );
            $productAttribute->expects($this->any())
                ->method('getId')
                ->will($this->returnValue('attribute_id_' . $i));
            $productAttribute->expects($this->any())
                ->method('getAttributeCode')
                ->will($this->returnValue('attribute_code_' . $i));
            $attribute->expects($this->any())
                ->method('getProductAttribute')
                ->will($this->returnValue($productAttribute));
            $attributes[] = $attribute;
        }
        $typeInstanceMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable', [], [], '', false
        );
        $typeInstanceMock->expects($this->any())
            ->method('getConfigurableAttributes')
            ->will($this->returnValue($attributes));
        $currentProductMock->expects($this->any())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstanceMock));
        $allowedProducts = [];
        for ($i = 1; $i <= 2; $i++) {
            $productMock = $this->getMock(
                'Magento\Catalog\Model\Product', ['getData', 'getImage', 'getId', '__wakeup', 'getMediaGalleryImages'], [], '', false
            );
            $productMock->expects($this->any())
                ->method('getData')
                ->will($this->returnCallback([$this, 'getDataCallback']));
            $productMock->expects($this->any())
                ->method('getId')
                ->will($this->returnValue('product_id_' . $i));
            if ($i == 2) {
                $productMock->expects($this->any())
                    ->method('getImage')
                    ->will($this->returnValue(true));
            }
            $allowedProducts[] = $productMock;
        }
        $provider[] = [
            [
                'attribute_id_1' => [
                    'attribute_code_value_1' => ['product_id_1', 'product_id_2'],
                ],
                'index' => [
                    'product_id_1' => [
                        'attribute_id_1' => 'attribute_code_value_1',
                        'attribute_id_2' => 'attribute_code_value_2'
                    ],

                    'product_id_2' => [
                        'attribute_id_1' => 'attribute_code_value_1',
                        'attribute_id_2' => 'attribute_code_value_2'
                    ]

                ],
                'attribute_id_2' => [
                    'attribute_code_value_2' => ['product_id_1', 'product_id_2'],
                ],
            ],
            [
                'allowed_products' => $allowedProducts,
                'current_product_mock' => $currentProductMock,
            ],
        ];
        return $provider;
    }

    /**
     * @param string $key
     * @return string
     */
    public function getDataCallback($key)
    {
        $map = [];
        for ($k = 1; $k < 3; $k++) {
            $map['attribute_code_' . $k] = 'attribute_code_value_' . $k;
        }
        return $map[$key];
    }
}

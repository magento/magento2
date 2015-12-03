<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\ConfigurableProduct\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

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

    /**
     * @var \Magento\Framework\Data\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaGalleryCollection;

    protected function setUp()
    {
        $this->_imageHelperMock = $this->getMock('Magento\Catalog\Helper\Image', [], [], '', false);
        $this->_productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->mediaGalleryCollection = $this->getMockBuilder('Magento\Framework\Data\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);

        $this->_model = $this->objectManager->getObject(
            'Magento\ConfigurableProduct\Helper\Data',
            [
                'imageHelper' => $this->_imageHelperMock
            ]
        );
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

    public function testGetOptions()
    {
        $currentProductMock = $this->getMock(
            'Magento\Catalog\Model\Product', ['getTypeInstance', '__wakeup'], [], '', false
        );
        $attributesCount = 3;
        $attributes = [];
        for ($i = 1; $i < $attributesCount; $i++) {
            $attribute = $this->getMock(
                'Magento\Framework\Object', ['getProductAttribute'], [], '', false
            );
            $productAttribute = $this->getMock(
                'Magento\Framework\Object',
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
                'Magento\Catalog\Model\Product',
                ['getData', 'getImage', 'getId', '__wakeup', 'getMediaGalleryImages'], [], '', false
            );
            $imageFile = 'http://example.com/base_img_url';
            $imageItem = $this->objectManager->getObject(
                'Magento\Framework\Object',
                [
                    'data' => ['file' => $imageFile]
                ]
            );
            $this->mediaGalleryCollection->expects($this->any())
                ->method('getIterator')
                ->willReturn(new \ArrayIterator([$imageItem]));
            $productMock->expects($this->any())
                ->method('getMediaGalleryImages')
                ->willReturn($this->mediaGalleryCollection);
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

        $expected = [
            'images' => [
                'product_id_1' => ['http://example.com/base_img_url_1'],
                'product_id_2' => ['http://example.com/base_img_url_2']
            ],
            'attribute_id_1' => [
                'attribute_code_value_1' => ['product_id_1', 'product_id_2'],
            ],
            'attribute_id_2' => [
                'attribute_code_value_2' => ['product_id_1', 'product_id_2'],
            ],
            'index' => [
                'product_id_1' => [
                    'attribute_id_1' => 'attribute_code_value_1',
                    'attribute_id_2' => 'attribute_code_value_2',
                ],

                'product_id_2' => [
                    'attribute_id_1' => 'attribute_code_value_1',
                    'attribute_id_2' => 'attribute_code_value_2',
                ]
            ],
        ];
        $this->_imageHelperMock->expects($this->any())
            ->method('init')
            ->willReturnSelf();

        $this->_imageHelperMock->expects($this->any())
            ->method('setImageFile')
            ->will($this->returnCallback(function ($baseUrl) {
                static $i = 0;
                return $baseUrl . '_' . ++$i;
            }));

        $this->assertEquals(
            $expected,
            $this->_model->getOptions($currentProductMock, $allowedProducts)
        );
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

    public function testGetOptionsEmptyImages()
    {
        $expected = [];
        $allowedProducts = [];
        $currentProductMock = $this->getMock(
            'Magento\Catalog\Model\Product', ['getTypeInstance', '__wakeup'], [], '', false
        );

        $this->assertEquals(
            $expected,
            $this->_model->getOptions($currentProductMock, $allowedProducts)
        );
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Gallery;

/**
 * Unit test for catalog product Media Gallery  ReadHandler
 */
class ReadHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Model\Product\Gallery\ReadHandler */
    protected $model;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeRepository;

    protected function setUp()
    {
        $this->objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->attributeRepository = $this->getMock(
            'Magento\Catalog\Model\Product\Attribute\Repository',
            ['get'],
            [],
            '',
            false
        );
        $this->model = $this->objectHelper->getObject(
            \Magento\Catalog\Model\Product\Gallery\ReadHandler::class,
            [
                'attributeRepository' => $this->attributeRepository,
            ]
        );
    }

    public function testAddMediaDataToProduct()
    {
        $attribute = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $attribute->expects($this->any())->method('getAttributeCode')->will($this->returnValue('image'));

        $this->attributeRepository->expects($this->once())
            ->method('get')
            ->with('media_gallery')
            ->willReturn($attribute);

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())->method('setData')->with(
            'image',
            [
                'images' =>[
                   10 => ['value_id' => 10,]
                ],
                'values' => []
            ]
        );
        $this->model->addMediaDataToProduct($product, [['value_id' => 10]]);
    }
}

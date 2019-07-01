<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Config\CatalogClone\Media;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ImageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Config\CatalogClone\Media\Image
     */
    private $model;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeCollection;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attribute;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaperMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->eavConfig = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeCollection = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeCollectionFactory = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeCollectionFactory->expects($this->any())->method('create')->will(
            $this->returnValue($this->attributeCollection)
        );

        $this->attribute = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->escaperMock = $this->getMockBuilder(
            \Magento\Framework\Escaper::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['escapeHtml'])
            ->getMock();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            \Magento\Catalog\Model\Config\CatalogClone\Media\Image::class,
            [
                'eavConfig' => $this->eavConfig,
                'attributeCollectionFactory' => $this->attributeCollectionFactory,
                'escaper' => $this->escaperMock,
            ]
        );
    }

    /**
     * @param string $actualLabel
     * @param string $expectedLabel
     * @return void
     * @dataProvider getPrefixesDataProvider
     */
    public function testGetPrefixes(string $actualLabel, string $expectedLabel)
    {
        $entityTypeId = 3;
        /** @var \Magento\Eav\Model\Entity\Type|\PHPUnit_Framework_MockObject_MockObject $entityType */
        $entityType = $this->getMockBuilder(\Magento\Eav\Model\Entity\Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityType->expects($this->once())->method('getId')->willReturn($entityTypeId);

        /** @var AbstractFrontend|\PHPUnit_Framework_MockObject_MockObject $frontend */
        $frontend = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend::class)
            ->setMethods(['getLabel'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $frontend->expects($this->once())->method('getLabel')->willReturn($actualLabel);

        $this->attributeCollection->expects($this->once())->method('setEntityTypeFilter')->with($entityTypeId);
        $this->attributeCollection->expects($this->once())->method('setFrontendInputTypeFilter')->with('media_image');

        $this->attribute->expects($this->once())->method('getAttributeCode')->willReturn('attributeCode');
        $this->attribute->expects($this->once())->method('getFrontend')->willReturn($frontend);

        $this->attributeCollection->expects($this->any())->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->attribute]));

        $this->eavConfig->expects($this->any())->method('getEntityType')->with(Product::ENTITY)
            ->willReturn($entityType);

        $this->escaperMock->expects($this->once())->method('escapeHtml')->with($actualLabel)
            ->willReturn($expectedLabel);

        $this->assertEquals([['field' => 'attributeCode_', 'label' => $expectedLabel]], $this->model->getPrefixes());
    }

    /**
     * @return array
     */
    public function getPrefixesDataProvider(): array
    {
        return [
            [
                'actual_label' => 'testLabel',
                'expected_label' => 'testLabel',
            ],
            [
                'actual_label' => '<media-image-attributelabel',
                'expected_label' => '&lt;media-image-attributelabel',
            ],
        ];
    }
}

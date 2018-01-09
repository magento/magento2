<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Eav\Model\TypeLocator;
use Magento\Eav\Model\TypeLocator\ComplexType as ComplexTypeLocator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit test class for \Magento\Eav\Model\TypeLocator
 */
class TypeLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TypeLocator
     */
    private $customAttributeTypeLocator;

    /**
     * @var ObjectManager
     */
    private $objectManger;

    /**
     * @var ComplexTypeLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $complexType;

    protected function setUp()
    {
        $this->objectManger = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->complexType = $this->getMockBuilder(ComplexTypeLocator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customAttributeTypeLocator = $this->objectManger->getObject(
            TypeLocator::class,
            [
                'typeLocators' => [$this->complexType]
            ]
        );
    }

    /**
     * Test getType method
     *
     * @param string $attributeCode
     * @param string $serviceClass
     * @param array $attributeRepositoryResponse
     * @param \Magento\Framework\Stdlib\StringUtils $stringUtility,
     * @param array $serviceEntityTypeMapData
     * @param array $serviceBackendModelDataInterfaceMapData
     * @param string $expected
     * @dataProvider getTypeDataProvider
     */
    public function testGetType(
        $attributeCode,
        $serviceClass,
        $serviceEntityTypeMapData,
        $expected
    ) {
        $this->complexType->expects($this->once())->method('getType')->willReturn($expected);
        $type = $this->customAttributeTypeLocator->getType(
            $attributeCode,
            $serviceEntityTypeMapData[$serviceClass]
        );

        $this->assertEquals($expected, $type, 'Expected: ' . $expected . 'but got: ' . $type);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getTypeDataProvider()
    {
        $serviceInterface = \Magento\Catalog\Api\Data\ProductInterface::class;
        $eavEntityType = 'catalog_product';
        $mediaBackEndModelClass = ProductAttributeMediaGalleryEntryInterface::class;
        $mediaAttributeDataInterface = ProductAttributeMediaGalleryEntryInterface::class;

        $attribute = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            ['getBackendModel']
        );

        $attribute->expects($this->any())
            ->method('getBackendModel')
            ->willReturn($mediaBackEndModelClass);

        $attributeNoBackendModel = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            ['getBackendModel', 'getFrontendInput']
        );

        $attributeNoBackendModel->expects($this->any())
            ->method('getBackendModel')
            ->willReturn(null);

        $attributeNoBackendModel->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn('image');

        return [
            [
                'attributeCode' => 'media_galley',
                'serviceClass' => $serviceInterface,
                'serviceEntityTypeMapData' => [$serviceInterface => $eavEntityType],
                'expected' => $mediaAttributeDataInterface
            ],
            [
                'attributeCode' => null,
                'serviceClass' => $serviceInterface,
                'serviceEntityTypeMapData' => [$serviceInterface => $eavEntityType],
                'expected' => 'anyType'
            ],
        ];
    }
}

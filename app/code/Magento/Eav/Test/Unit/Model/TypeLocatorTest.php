<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\TypeLocator;
use Magento\Eav\Model\TypeLocator\ComplexType as ComplexTypeLocator;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test class for \Magento\Eav\Model\TypeLocator
 */
class TypeLocatorTest extends TestCase
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
     * @var ComplexTypeLocator|MockObject
     */
    private $complexType;

    protected function setUp(): void
    {
        $this->objectManger = new ObjectManager($this);
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
     * @param StringUtils $stringUtility ,
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
    public static function getTypeDataProvider()
    {
        $serviceInterface = ProductInterface::class;
        $eavEntityType = 'catalog_product';
//        $mediaBackEndModelClass = ProductAttributeMediaGalleryEntryInterface::class;
        $mediaAttributeDataInterface = ProductAttributeMediaGalleryEntryInterface::class;

        // There is no use of below mock, uncomment to paas in result
//        $attribute = $this->createPartialMock(
//            Attribute::class,
//            ['getBackendModel']
//        );
//
//        $attribute->expects($this->any())
//            ->method('getBackendModel')
//            ->willReturn($mediaBackEndModelClass);
//
//        $attributeNoBackendModel = $this->createPartialMock(
//            Attribute::class,
//            ['getBackendModel', 'getFrontendInput']
//        );
//
//        $attributeNoBackendModel->expects($this->any())
//            ->method('getBackendModel')
//            ->willReturn(null);
//
//        $attributeNoBackendModel->expects($this->any())
//            ->method('getFrontendInput')
//            ->willReturn('image');

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

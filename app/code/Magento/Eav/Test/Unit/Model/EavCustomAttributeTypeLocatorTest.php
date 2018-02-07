<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Eav\Test\Unit\Model;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\EavCustomAttributeTypeLocator;
use Magento\Eav\Model\EavCustomAttributeTypeLocator\SimpleType as SimpleTypeLocator;
use Magento\Eav\Model\EavCustomAttributeTypeLocator\ComplexType as ComplexTypeLocator;

/**
 * Unit test class for \Magento\Eav\Model\EavCustomAttributeTypeLocator
 */
class EavCustomAttributeTypeLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EavCustomAttributeTypeLocator
     */
    private $eavCustomAttributeTypeLocator;

    /**
     * @var AttributeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeRepository = [];

    protected function setUp()
    {
        $this->attributeRepository = $this->getMock(
            'Magento\Eav\Model\AttributeRepository',
            ['get'],
            [],
            '',
            false
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
        $attributeRepositoryResponse,
        $stringUtility,
        $serviceEntityTypeMapData,
        $serviceBackendModelDataInterfaceMapData,
        $expected
    ) {
        $this->attributeRepository
            ->expects($this->any())
            ->method('get')
            ->willReturn($attributeRepositoryResponse);
        
        $this->eavCustomAttributeTypeLocator = new EavCustomAttributeTypeLocator(
            $this->attributeRepository,
            $stringUtility,
            $serviceEntityTypeMapData,
            $serviceBackendModelDataInterfaceMapData
        );

        $simpleTypeLocator = new SimpleTypeLocator();
        // Todo: \Magento\Framework\TestFramework\Unit\Helper\ObjectManager to do this automatically (MAGETWO-49793)
        $reflection = new \ReflectionClass(get_class($this->eavCustomAttributeTypeLocator));
        $reflectionProperty = $reflection->getProperty('simpleTypeLocator');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->eavCustomAttributeTypeLocator, $simpleTypeLocator);

        $complexTypeLocator = new ComplexTypeLocator($stringUtility);
        // Todo: \Magento\Framework\TestFramework\Unit\Helper\ObjectManager to do this automatically (MAGETWO-49793)
        $reflection = new \ReflectionClass(get_class($this->eavCustomAttributeTypeLocator));
        $reflectionProperty = $reflection->getProperty('complexTypeLocator');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->eavCustomAttributeTypeLocator, $complexTypeLocator);

        $type = $this->eavCustomAttributeTypeLocator->getType($attributeCode, $serviceClass);

        $this->assertEquals($expected, $type, 'Expected: ' . $expected . 'but got: ' . $type);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getTypeDataProvider()
    {
        $serviceInterface = 'Magento\Catalog\Api\Data\ProductInterface';
        $eavEntityType = 'catalog_product';
        $mediaBackEndModelClass = 'Magento\Catalog\Model\Product\Attribute\Backend\Media';
        $mediaAttributeDataInterface = '\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface';
        $serviceBackendModelDataInterfaceMapData = [
            $serviceInterface => [$mediaBackEndModelClass => $mediaAttributeDataInterface]
        ];

        $attribute = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Eav\Attribute',
            ['getBackendModel'],
            [],
            '',
            false
        );

        $attribute->expects($this->any())
            ->method('getBackendModel')
            ->willReturn($mediaBackEndModelClass);

        $attributeNoBackendModel = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Eav\Attribute',
            ['getBackendModel', 'getFrontendInput'],
            [],
            '',
            false
        );

        $attributeNoBackendModel->expects($this->any())
            ->method('getBackendModel')
            ->willReturn(null);

        $attributeNoBackendModel->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn('image');

        $stringUtility = new \Magento\Framework\Stdlib\StringUtils();

        return [
            [
                'attributeCode' => 'media_galley',
                'serviceClass' => $serviceInterface,
                'attributeRepositoryResponse' => $attribute,
                'stringUtility' => $stringUtility,
                'serviceEntityTypeMapData' => [$serviceInterface => $eavEntityType],
                'serviceBackendModelDataInterfaceMapData' => $serviceBackendModelDataInterfaceMapData,
                'expected' => $mediaAttributeDataInterface
            ],
            [
                'attributeCode' => null,
                'serviceClass' => $serviceInterface,
                'attributeRepositoryResponse' => $attribute,
                'stringUtility' => $stringUtility,
                'serviceEntityTypeMapData' => [$serviceInterface => $eavEntityType],
                'serviceBackendModelDataInterfaceMapData' => $serviceBackendModelDataInterfaceMapData,
                'expected' => 'mixed'
            ],
            [
                'attributeCode' => 'media_galley',
                'serviceClass' => null,
                'attributeRepositoryResponse' => $attribute,
                'stringUtility' => $stringUtility,
                'serviceEntityTypeMapData' => [$serviceInterface => $eavEntityType],
                'serviceBackendModelDataInterfaceMapData' => $serviceBackendModelDataInterfaceMapData,
                'expected' => 'mixed'
            ],
            [
                'attributeCode' => 'media_galley',
                'serviceClass' => $serviceInterface,
                'attributeRepositoryResponse' => $attributeNoBackendModel,
                'stringUtility' => $stringUtility,
                'serviceEntityTypeMapData' => [],
                'serviceBackendModelDataInterfaceMapData' => [],
                'expected' => 'mixed'
            ],
            [
                'attributeCode' => 'media_galley',
                'serviceClass' => 'Magento\Catalog\Api\Data\ProductInterface',
                'attributeRepositoryResponse' => $attribute,
                'stringUtility' => $stringUtility,
                'serviceEntityTypeMapData' => [$serviceInterface => $eavEntityType],
                'serviceBackendModelDataInterfaceMapData' => [],
                'expected' => 'mixed'
            ],
            [
                'attributeCode' => 'image',
                'serviceClass' => $serviceInterface,
                'attributeRepositoryResponse' => $attributeNoBackendModel,
                'stringUtility' => $stringUtility,
                'serviceEntityTypeMapData' => [$serviceInterface => 'image'],
                'serviceBackendModelDataInterfaceMapData' =>
                    [
                        $serviceInterface =>
                            [
                                'Magento\Eav\Model\Attribute\Data\Image' => $mediaAttributeDataInterface
                            ]
                    ],
                'expected' => $mediaAttributeDataInterface
            ]
        ];
    }

    public function testGetTypeIfAttributeDoesNotExist()
    {
        $this->attributeRepository
            ->expects($this->any())
            ->method('get')
            ->willReturn(new \Magento\Framework\Exception\NoSuchEntityException());

        $this->eavCustomAttributeTypeLocator = new EavCustomAttributeTypeLocator(
            $this->attributeRepository,
            new \Magento\Framework\Stdlib\StringUtils(),
            [],
            []
        );

        $this->assertEquals(
            'mixed',
            $this->eavCustomAttributeTypeLocator->getType('media_galley', 'Magento\Catalog\Api\Data\ProductInterface')
        );
    }

    public function testGetAllServiceDataInterfaceEmpty()
    {
        $stringUtility = new \Magento\Framework\Stdlib\StringUtils();
        $this->eavCustomAttributeTypeLocator = new EavCustomAttributeTypeLocator(
            $this->attributeRepository,
            $stringUtility
        );
        $this->assertEmpty($this->eavCustomAttributeTypeLocator->getAllServiceDataInterfaces());
    }

    public function testGetAllServiceDataInterface()
    {
        $serviceBackendModelDataInterfaceMapData = [
            'ServiceA' => ['BackendA' => 'ServiceDataInterfaceA'],
            'ServiceB' => ['BackendB' => 'ServiceDataInterfaceB', 'BackendC' => 'ServiceDataInterfaceC'],
            'ServiceC' => ['BackendD' => 'ServiceDataInterfaceD']
        ];
        $stringUtility = new \Magento\Framework\Stdlib\StringUtils();
        $this->eavCustomAttributeTypeLocator = new EavCustomAttributeTypeLocator(
            $this->attributeRepository, $stringUtility, [], $serviceBackendModelDataInterfaceMapData
        );
        $this->assertEquals(
            ['ServiceDataInterfaceA', 'ServiceDataInterfaceB', 'ServiceDataInterfaceC', 'ServiceDataInterfaceD'],
            $this->eavCustomAttributeTypeLocator->getAllServiceDataInterfaces()
        );
    }
}

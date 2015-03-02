<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model;

use Magento\Eav\Api\AttributeRepositoryInterface;

class EavCustomAttributeTypeLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EavCustomAttributeTypeLocator
     */
    private $eavCustomAttributeTypeLocator;

    /**
     * @var AttributeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeRepositoryMock;

    /**
     * @var \Magento\Framework\Object|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serviceEntityTypeMapMock;

    /**
     * @var \Magento\Framework\Object|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serviceBackendModelDataInterfaceMapMock;


    protected function setUp()
    {
        $this->attributeRepositoryMock = $this->getMock(
            'Magento\Eav\Model\AttributeRepository',
            ['get'],
            [],
            '',
            false
        );

        $this->serviceEntityTypeMapMock = $this->getMock(
            'Magento\Framework\Object',
            ['getData'],
            [],
            '',
            false
        );

        $this->serviceBackendModelDataInterfaceMapMock = $this->getMock(
            'Magento\Framework\Object',
            ['getData'],
            [],
            '',
            false
        );

        $this->eavCustomAttributeTypeLocator = new EavCustomAttributeTypeLocator(
            $this->attributeRepositoryMock,
            $this->serviceEntityTypeMapMock,
            $this->serviceBackendModelDataInterfaceMapMock
        );
    }

    /**
     * Test getType method
     *
     * @param array $attributeRepositoryResponse
     * @param array $serviceEntityTypeMapData
     * @param array $serviceBackendModelDataInterfaceMapData
     * @param string $expected
     * @dataProvider getTypeDataProvider
     */
    public function testGetType(
        $attributeRepositoryResponse,
        $serviceEntityTypeMapData,
        $serviceBackendModelDataInterfaceMapData,
        $expected
    ) {
        $this->attributeRepositoryMock
            ->expects($this->any())
            ->method('get')
            ->willReturn($attributeRepositoryResponse);

        $this->serviceEntityTypeMapMock
            ->expects($this->any())
            ->method('getData')
            ->willReturn($serviceEntityTypeMapData);

        $this->serviceBackendModelDataInterfaceMapMock
            ->expects($this->any())
            ->method('getData')
            ->willReturn($serviceBackendModelDataInterfaceMapData);

        $type = $this->eavCustomAttributeTypeLocator->getType(
            'media_galley',
            'Magento\Catalog\Api\Data\ProductInterface'
        );

        $this->assertEquals($expected, $type, 'Expected: ' . $expected . 'but got: ' . $type);
    }

    public function getTypeDataProvider()
    {
        $serviceInterface = 'Magento\Catalog\Api\Data\ProductInterface';
        $eavEntityType = 'product';
        $mediaBackEndModelClass = 'Magento\Catalog\Model\Product\Attribute\Backend\Media';
        $mediaAttributeDataInterface = '\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface';

        $attribute = $this->getMock(
            'Magento\Catalog\Model\Resource\Eav\Attribute',
            ['getBackendModel'],
            [],
            '',
            false
        );

        $attribute->expects($this->any())
            ->method('getBackendModel')
            ->willReturn($mediaBackEndModelClass);

        $attributeNoBackendModel = $this->getMock(
            'Magento\Catalog\Model\Resource\Eav\Attribute',
            ['getBackendModel'],
            [],
            '',
            false
        );

        $attributeNoBackendModel->expects($this->any())
            ->method('getBackendModel')
            ->willReturn(null);

        return [
            [
                'attributeRepositoryResponse' => $attribute,
                'serviceEntityTypeMapData' => [$serviceInterface => $eavEntityType],
                'serviceBackendModelDataInterfaceMapData' => [$mediaBackEndModelClass => $mediaAttributeDataInterface],
                'expected' => $mediaAttributeDataInterface
            ],
            [
                'attributeRepositoryResponse' => $attributeNoBackendModel,
                'serviceEntityTypeMapData' => [],
                'serviceBackendModelDataInterfaceMapData' => [],
                'expected' => null
            ],
            [
                'attributeRepositoryResponse' => $attributeNoBackendModel,
                'serviceEntityTypeMapData' => null,
                'serviceBackendModelDataInterfaceMapData' => [],
                'expected' => null
            ],
            [
                'attributeRepositoryResponse' => $attribute,
                'serviceEntityTypeMapData' => [$serviceInterface => $eavEntityType],
                'serviceBackendModelDataInterfaceMapData' => null,
                'expected' => null
            ],
        ];
    }
}

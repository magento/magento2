<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\Product;

class LinkTypeProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\LinkTypeProvider
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkTypeFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkAttributeFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkFactoryMock;

    /**
     * @var Array
     */
    protected $linkTypes;

    protected function setUp()
    {
        $this->linkTypeFactoryMock = $this->createPartialMock(\Magento\Catalog\Api\Data\ProductLinkTypeInterfaceFactory::class, ['create']);
        $this->linkAttributeFactoryMock = $this->createPartialMock(\Magento\Catalog\Api\Data\ProductLinkAttributeInterfaceFactory::class, ['create']);
        $this->linkFactoryMock = $this->createPartialMock(\Magento\Catalog\Model\Product\LinkFactory::class, ['create']);
        $this->linkTypes = [
            'test_product_link_1' => 'test_code_1',
            'test_product_link_2' => 'test_code_2',
            'test_product_link_3' => 'test_code_3',
        ];
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\Product\LinkTypeProvider::class,
            [
                'linkTypeFactory' => $this->linkTypeFactoryMock,
                'linkAttributeFactory' => $this->linkAttributeFactoryMock,
                'linkFactory' => $this->linkFactoryMock,
                'linkTypes' => $this->linkTypes
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testGetItems()
    {
        $expectedResult = [];
        $linkTypeMocks = [];
        foreach ($this->linkTypes as $type => $typeCode) {
            $value = ['name' => $type, 'code' => $typeCode];
            $linkTypeMock = $this->createMock(\Magento\Catalog\Api\Data\ProductLinkTypeInterface::class);
            $linkTypeMock->expects($this->once())
                ->method('setName')
                ->with($type)
                ->willReturnSelf();
            $linkTypeMock->expects($this->once())
                ->method('setCode')
                ->with($typeCode)
                ->willReturnSelf();
            $linkTypeMocks[] = $linkTypeMock;
            $expectedResult[] = $linkTypeMock;
        }
        $this->linkTypeFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->will($this->onConsecutiveCalls($linkTypeMocks[0], $linkTypeMocks[1], $linkTypeMocks[2]));
        $this->assertEquals($expectedResult, $this->model->getItems());
    }

    /**
     * @dataProvider getItemAttributesDataProvider
     */
    public function testGetItemAttributes($type, $typeId)
    {
        $attributes = [
            ['code' => 'test_code_1', 'type' => 'test_type_1'],
        ];
        $linkAttributeMock = $this->createMock(\Magento\Catalog\Api\Data\ProductLinkAttributeInterface::class);
        $linkAttributeMock->expects($this->once())
            ->method('setCode')
            ->with($attributes[0]['code'])
            ->willReturnSelf();
        $linkAttributeMock->expects($this->once())
            ->method('setType')
            ->with($attributes[0]['type'])
            ->willReturnSelf();
        $expectedResult = [
            $linkAttributeMock,
        ];
        $linkMock = $this->createPartialMock(\Magento\Catalog\Model\Product\Link::class, ['getAttributes']);
        $linkMock->expects($this->once())->method('getAttributes')->willReturn($attributes);
        $this->linkFactoryMock->expects($this->once())->method('create')->with($typeId)->willReturn($linkMock);
        $this->linkAttributeFactoryMock->expects($this->once())->method('create')->willReturn($linkAttributeMock);
        $this->assertEquals($expectedResult, $this->model->getItemAttributes($type));
    }

    /**
     * @return array
     */
    public function getItemAttributesDataProvider()
    {
        return [
            ['test_product_link_2', ['data' => ['link_type_id' => 'test_code_2']]],
            ['null_product', ['data' => ['link_type_id' => null]]]
        ];
    }
}

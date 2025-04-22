<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Catalog\Api\Data\ProductLinkAttributeInterface;
use Magento\Catalog\Api\Data\ProductLinkAttributeInterfaceFactory;
use Magento\Catalog\Api\Data\ProductLinkTypeInterface;
use Magento\Catalog\Api\Data\ProductLinkTypeInterfaceFactory;
use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\Product\LinkFactory;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinkTypeProviderTest extends TestCase
{
    /**
     * @var LinkTypeProvider
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $linkTypeFactoryMock;

    /**
     * @var MockObject
     */
    protected $linkAttributeFactoryMock;

    /**
     * @var MockObject
     */
    protected $linkFactoryMock;

    /**
     * @var Array
     */
    protected $linkTypes;

    protected function setUp(): void
    {
        $this->linkTypeFactoryMock = $this->createPartialMock(
            ProductLinkTypeInterfaceFactory::class,
            ['create']
        );
        $this->linkAttributeFactoryMock = $this->createPartialMock(
            ProductLinkAttributeInterfaceFactory::class,
            ['create']
        );
        $this->linkFactoryMock = $this->createPartialMock(
            LinkFactory::class,
            ['create']
        );
        $this->linkTypes = [
            'test_product_link_1' => 'test_code_1',
            'test_product_link_2' => 'test_code_2',
            'test_product_link_3' => 'test_code_3',
        ];
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            LinkTypeProvider::class,
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
            $linkTypeMock = $this->getMockForAbstractClass(ProductLinkTypeInterface::class);
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
        $linkAttributeMock = $this->getMockForAbstractClass(ProductLinkAttributeInterface::class);
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
        $linkMock = $this->createPartialMock(Link::class, ['getAttributes']);
        $linkMock->expects($this->once())->method('getAttributes')->willReturn($attributes);
        $this->linkFactoryMock->expects($this->once())->method('create')->with($typeId)->willReturn($linkMock);
        $this->linkAttributeFactoryMock->expects($this->once())->method('create')->willReturn($linkAttributeMock);
        $this->assertEquals($expectedResult, $this->model->getItemAttributes($type));
    }

    /**
     * @return array
     */
    public static function getItemAttributesDataProvider()
    {
        return [
            ['test_product_link_2', ['data' => ['link_type_id' => 'test_code_2']]],
            ['null_product', ['data' => ['link_type_id' => null]]]
        ];
    }
}

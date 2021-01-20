<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\Component\Listing\Columns;

use Magento\Catalog\Ui\Component\Listing\Columns\AttributeSetText;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;

/**
 * Class AttributeSetTextTest
 */
class AttributeSetTextTest extends AbstractColumnTest
{
    const ATTRIBUTE_SET_ID = 4;
    const ATTRIBUTE_SET_NAME = 'Default';

    /**
     * @var AttributeSetRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $attributeSetRepositoryMock;

    /**
     * @var AttributeSetInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $attributeSetMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeSetRepositoryMock = $this->getMockBuilder(AttributeSetRepositoryInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->attributeSetMock = $this->getMockBuilder(AttributeSetInterface::class)
            ->setMethods(['getAttributeSetName'])
            ->getMockForAbstractClass();
    }

    /**
     * @return AttributeSetText
     */
    protected function getModel()
    {
        return $this->objectManager->getObject(AttributeSetText::class, [
            'context' => $this->contextMock,
            'uiComponentFactory' => $this->uiComponentFactoryMock,
            'attributeSetRepository' => $this->attributeSetRepositoryMock,
            'components' => [],
            'data' => [],
        ]);
    }

    public function testPrepareDataSource()
    {
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        AttributeSetText::NAME => self::ATTRIBUTE_SET_ID,
                    ]
                ],
            ],
        ];
        $expectedDataSource = [
            'data' => [
                'items' => [
                    [
                        AttributeSetText::NAME => self::ATTRIBUTE_SET_ID,
                        '' => self::ATTRIBUTE_SET_NAME,
                    ]
                ],
            ],
        ];

        $this->attributeSetMock->expects($this->once())
            ->method('getAttributeSetName')
            ->willReturn(self::ATTRIBUTE_SET_NAME);
        $this->attributeSetRepositoryMock->expects($this->once())
            ->method('get')
            ->with(self::ATTRIBUTE_SET_ID)
            ->willReturn($this->attributeSetMock);

        $this->assertEquals($expectedDataSource, $this->getModel()->prepareDataSource($dataSource));
    }
}

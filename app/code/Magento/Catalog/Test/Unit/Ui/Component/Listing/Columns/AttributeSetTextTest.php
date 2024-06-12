<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\Component\Listing\Columns;

use Magento\Catalog\Ui\Component\Listing\Columns\AttributeSetText;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use PHPUnit\Framework\MockObject\MockObject;

class AttributeSetTextTest extends AbstractColumnTestCase
{
    private const ATTRIBUTE_SET_ID = 4;
    private const ATTRIBUTE_SET_NAME = 'Default';

    /**
     * @var AttributeSetRepositoryInterface|MockObject
     */
    protected $attributeSetRepositoryMock;

    /**
     * @var AttributeSetInterface|MockObject
     */
    protected $attributeSetMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeSetRepositoryMock = $this->getMockBuilder(AttributeSetRepositoryInterface::class)
            ->onlyMethods(['get'])
            ->getMockForAbstractClass();
        $this->attributeSetMock = $this->getMockBuilder(AttributeSetInterface::class)
            ->onlyMethods(['getAttributeSetName'])
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

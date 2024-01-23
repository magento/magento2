<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Ui\DataProvider;

use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Ui\DataProvider\Attributes;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\TestCase;

class AttributesTest extends TestCase
{
    /**
     * @var Collection
     */
    private Collection $collectionMock;

    /**
     * @var Select
     */
    private Select $selectMock;

    /**
     * @var Attributes
     */
    private Attributes $attributes;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where'])
            ->getMock();
        $collectionAttributeHandlerMock = $this->getMockBuilder(ConfigurableAttributeHandler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getApplicableAttributes'])
            ->getMock();
        $collectionAttributeHandlerMock->expects($this->once())
            ->method('getApplicableAttributes')
            ->willReturn($this->collectionMock);
        $this->attributes = new Attributes(
            'myName',
            'myPrimaryFieldName',
            'myRequestFieldName',
            $collectionAttributeHandlerMock
        );
    }

    /**
     * @return void
     */
    public function testGetData()
    {
        $expectedResult = [
            'totalRecords' => 1,
            'items' => [
                0 => ['attribute' => 'color']
            ]
        ];
        $this->collectionMock->expects($this->once())
            ->method('getSelect')
            ->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())
            ->method('where')
            ->with('(`apply_to` IS NULL) OR
            (
                FIND_IN_SET(' .
                    sprintf("'%s'", Type::TYPE_SIMPLE) . ',
                    `apply_to`
                ) AND
                FIND_IN_SET(' .
                    sprintf("'%s'", Type::TYPE_VIRTUAL) . ',
                    `apply_to`
                ) AND
                FIND_IN_SET(' .
                    sprintf("'%s'", Configurable::TYPE_CODE) . ',
                    `apply_to`
                )
             )')
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([new DataObject(['attribute' => 'color'])]);
        $this->collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(1);
        $this->assertEquals($expectedResult, $this->attributes->getData());
    }
}

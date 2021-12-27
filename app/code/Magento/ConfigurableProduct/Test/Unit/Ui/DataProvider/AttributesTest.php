<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Ui\DataProvider;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler;
use Magento\ConfigurableProduct\Ui\DataProvider\Attributes;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
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
        $objectManager = new ObjectManager($this);
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
        $this->attributes = $objectManager->getObject(
            Attributes::class,
            [
                'name' => 'myName',
                'primaryFieldName' => 'myPrimaryFieldName',
                'requestFieldName' => 'myRequestFieldName',
                'configurableAttributeHandler' => $collectionAttributeHandlerMock
            ]
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
        $this->collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([new DataObject(['attribute' => 'color'])]);
        $this->collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(1);
        $this->assertEquals($expectedResult, $this->attributes->getData());
    }
}

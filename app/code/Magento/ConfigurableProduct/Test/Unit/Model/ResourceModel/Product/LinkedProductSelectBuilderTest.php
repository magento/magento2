<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\ResourceModel\Product;

use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\LinkedProductSelectBuilder;

class LinkedProductSelectBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LinkedProductSelectBuilder
     */
    private $subject;

    /**
     * @var BaseSelectProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $baseSelectProcessorMock;

    /**
     * @var LinkedProductSelectBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $linkedProductSelectBuilderMock;

    protected function setUp()
    {
        $this->baseSelectProcessorMock = $this->getMockBuilder(BaseSelectProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->linkedProductSelectBuilderMock = $this->getMockBuilder(LinkedProductSelectBuilderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->subject = (new ObjectManager($this))->getObject(
            LinkedProductSelectBuilder::class,
            [
                'baseSelectProcessor' => $this->baseSelectProcessorMock,
                'linkedProductSelectBuilder' => $this->linkedProductSelectBuilderMock
            ]
        );
    }

    public function testBuild()
    {
        $productId = 42;

        /** @var Select|\PHPUnit_Framework_MockObject_MockObject $selectMock */
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $expectedResult = [$selectMock];

        $this->linkedProductSelectBuilderMock->expects($this->any())
            ->method('build')
            ->with($productId)
            ->willReturn($expectedResult);

        $this->baseSelectProcessorMock->expects($this->once())
            ->method('process')
            ->with($selectMock);

        $this->assertEquals($expectedResult, $this->subject->build($productId));
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml;

use Magento\Analytics\ReportXml\IteratorFactory;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class IteratorFactoryTest
 */
class IteratorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var \IteratorIterator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $iteratorIteratorMock;

    /**
     * @var IteratorFactory
     */
    private $iteratorFactory;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->iteratorIteratorMock = $this->getMockBuilder(\IteratorIterator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->iteratorFactory = new IteratorFactory(
            $this->objectManagerMock
        );
    }

    public function testCreate()
    {
        $arrayObject = new \ArrayIterator([1, 2, 3, 4, 5]);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\IteratorIterator::class, ['iterator' => $arrayObject])
            ->willReturn($this->iteratorIteratorMock);

        $this->assertEquals($this->iteratorFactory->create($arrayObject), $this->iteratorIteratorMock);
    }
}

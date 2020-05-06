<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\ReportXml;

use Magento\Analytics\ReportXml\IteratorFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IteratorFactoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var \IteratorIterator|MockObject
     */
    private $iteratorIteratorMock;

    /**
     * @var IteratorFactory
     */
    private $iteratorFactory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->iteratorIteratorMock = $this->createMock(\IteratorIterator::class);

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

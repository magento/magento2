<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\EngineResolverInterface;
use Magento\Search\Model\AdapterFactory;

class AdapterFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AdapterFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $adapterFactory;

    /**
     * @var ObjectManagerInterface |\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManager;

    /**
     * @var EngineResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $engineResolverMock;

    protected function setUp(): void
    {
        $this->engineResolverMock = $this->getMockBuilder(EngineResolverInterface::class)
            ->getMockForAbstractClass();

        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->adapterFactory = new AdapterFactory(
            $this->objectManager,
            ['ClassName' => 'ClassName'],
            $this->engineResolverMock
        );
    }

    public function testCreate()
    {
        $this->engineResolverMock->expects($this->once())->method('getCurrentSearchEngine')
            ->willReturn('ClassName');

        $adapter = $this->getMockBuilder(\Magento\Framework\Search\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager->expects($this->once())->method('create')
            ->with($this->equalTo('ClassName'), $this->equalTo(['input']))
            ->willReturn($adapter);

        $result = $this->adapterFactory->create(['input']);
        $this->assertInstanceOf(\Magento\Framework\Search\AdapterInterface::class, $result);
    }

    /**
     */
    public function testCreateExceptionThrown()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->engineResolverMock->expects($this->once())->method('getCurrentSearchEngine')
            ->willReturn('ClassName');

        $this->objectManager->expects($this->once())->method('create')
            ->with($this->equalTo('ClassName'), $this->equalTo(['input']))
            ->willReturn('t');

        $this->adapterFactory->create(['input']);
    }

    /**
     */
    public function testCreateLogicException()
    {
        $this->expectException(\LogicException::class);

        $this->engineResolverMock->expects($this->once())->method('getCurrentSearchEngine')
            ->willReturn('Class');

        $this->adapterFactory->create(['input']);
    }
}

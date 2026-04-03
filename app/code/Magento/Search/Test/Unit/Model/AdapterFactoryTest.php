<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\EngineResolverInterface;
use Magento\Search\Model\AdapterFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdapterFactoryTest extends TestCase
{
    /**
     * @var AdapterFactory|MockObject
     */
    private $adapterFactory;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var EngineResolverInterface|MockObject
     */
    private $engineResolverMock;

    protected function setUp(): void
    {
        $this->engineResolverMock = $this->getMockBuilder(EngineResolverInterface::class)
            ->getMock();

        $this->objectManager = $this->createMock(ObjectManagerInterface::class);

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

        $adapter = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager->expects($this->once())->method('create')
            ->with('ClassName', ['input'])
            ->willReturn($adapter);

        $result = $this->adapterFactory->create(['input']);
        $this->assertInstanceOf(AdapterInterface::class, $result);
    }

    public function testCreateExceptionThrown()
    {
        $this->expectException('InvalidArgumentException');
        $this->engineResolverMock->expects($this->once())->method('getCurrentSearchEngine')
            ->willReturn('ClassName');

        $this->objectManager->expects($this->once())->method('create')
            ->with('ClassName', ['input'])
            ->willReturn('t');

        $this->adapterFactory->create(['input']);
    }

    public function testCreateLogicException()
    {
        $this->expectException('LogicException');
        $this->engineResolverMock->expects($this->once())->method('getCurrentSearchEngine')
            ->willReturn('Class');

        $this->adapterFactory->create(['input']);
    }
}

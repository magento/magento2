<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedSearch\Test\Unit\Model\Client;

use InvalidArgumentException;
use LogicException;
use Magento\AdvancedSearch\Model\Client\ClientFactoryInterface;
use Magento\AdvancedSearch\Model\Client\ClientInterface;
use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\EngineResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\AdvancedSearch\Model\Client\ClientResolver
 */
class ClientResolverTest extends TestCase
{
    /**
     * Testable Object
     *
     * @var ClientResolver
     */
    private $model;

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
            ->getMockForAbstractClass();

        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->model = new ClientResolver(
            $this->objectManager,
            ['engineName' => 'engineFactoryClass'],
            ['engineName' => 'engineOptionClass'],
            $this->engineResolverMock
        );
    }

    public function testCreate(): void
    {
        $this->engineResolverMock->expects($this->once())->method('getCurrentSearchEngine')
            ->willReturn('engineName');

        $factoryMock = $this->getMockForAbstractClass(ClientFactoryInterface::class);

        $clientMock = $this->getMockForAbstractClass(ClientInterface::class);

        $clientOptionsMock = $this->getMockForAbstractClass(ClientOptionsInterface::class);

        $this->objectManager->expects($this->exactly(2))->method('create')
            ->withConsecutive(
                [$this->equalTo('engineFactoryClass')],
                [$this->equalTo('engineOptionClass')]
            )
            ->willReturnOnConsecutiveCalls(
                $factoryMock,
                $clientOptionsMock
            );

        $clientOptionsMock->expects($this->once())->method('prepareClientOptions')
            ->with([])
            ->willReturn(['parameters']);

        $factoryMock->expects($this->once())->method('create')
            ->with(['parameters'])
            ->willReturn($clientMock);

        $result = $this->model->create();
        $this->assertInstanceOf(ClientInterface::class, $result);
    }

    public function testCreateExceptionThrown(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->objectManager->expects($this->once())->method('create')
            ->with('engineFactoryClass')
            ->willReturn('t');

        $this->model->create('engineName');
    }

    public function testCreateLogicException(): void
    {
        $this->expectException(LogicException::class);
        $this->model->create('input');
    }

    public function testGetCurrentEngine(): void
    {
        $this->engineResolverMock->expects($this->once())->method('getCurrentSearchEngine')
            ->willReturn('engineName');

        $this->assertEquals('engineName', $this->model->getCurrentEngine());
    }
}

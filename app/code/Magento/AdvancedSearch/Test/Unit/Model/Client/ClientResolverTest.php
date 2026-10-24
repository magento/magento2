<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AdvancedSearch\Test\Unit\Model\Client;

use PHPUnit\Framework\Attributes\CoversClass;
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

#[CoversClass(ClientResolver::class)]
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
        $this->engineResolverMock = $this->createMock(EngineResolverInterface::class);

        $this->objectManager = $this->createMock(ObjectManagerInterface::class);

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

        $factoryMock = $this->createMock(ClientFactoryInterface::class);

        $clientMock = $this->createMock(ClientInterface::class);

        $clientOptionsMock = $this->createMock(ClientOptionsInterface::class);

        $this->objectManager->expects($this->exactly(2))->method('create')
            ->willReturnCallback(function ($className) use ($factoryMock, $clientOptionsMock) {
                if ($className == 'engineFactoryClass') {
                    return $factoryMock;
                } elseif ($className == 'engineOptionClass') {
                    return $clientOptionsMock;
                }
            });

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

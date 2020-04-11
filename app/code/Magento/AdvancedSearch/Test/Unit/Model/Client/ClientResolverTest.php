<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedSearch\Test\Unit\Model\Client;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\AdvancedSearch\Model\Client\ClientFactoryInterface;
use Magento\AdvancedSearch\Model\Client\ClientInterface;
use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\EngineResolverInterface;

class ClientResolverTest extends TestCase
{
    /**
     * @var ClientResolver|MockObject
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

        $this->objectManager = $this->createMock(ObjectManagerInterface::class);

        $this->model = new ClientResolver(
            $this->objectManager,
            ['engineName' => 'engineFactoryClass'],
            ['engineName' => 'engineOptionClass'],
            $this->engineResolverMock
        );
    }

    public function testCreate()
    {
        $this->engineResolverMock->expects($this->once())->method('getCurrentSearchEngine')
            ->will($this->returnValue('engineName'));

        $factoryMock = $this->createMock(ClientFactoryInterface::class);

        $clientMock = $this->createMock(ClientInterface::class);

        $clientOptionsMock = $this->createMock(ClientOptionsInterface::class);

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
            ->will($this->returnValue(['parameters']));

        $factoryMock->expects($this->once())->method('create')
            ->with($this->equalTo(['parameters']))
            ->will($this->returnValue($clientMock));

        $result = $this->model->create();
        $this->assertInstanceOf(ClientInterface::class, $result);
    }

    public function testCreateExceptionThrown()
    {
        $this->expectException('InvalidArgumentException');
        $this->objectManager->expects($this->once())->method('create')
            ->with($this->equalTo('engineFactoryClass'))
            ->will($this->returnValue('t'));

        $this->model->create('engineName');
    }

    public function testCreateLogicException()
    {
        $this->expectException('LogicException');
        $this->model->create('input');
    }

    public function testGetCurrentEngine()
    {
        $this->engineResolverMock->expects($this->once())->method('getCurrentSearchEngine')
            ->will($this->returnValue('engineName'));

        $this->assertEquals('engineName', $this->model->getCurrentEngine());
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Bulk;

use Magento\Framework\Amqp\Bulk\Exchange;
use Magento\Framework\MessageQueue\Bulk\ExchangeFactory;
use Magento\Framework\MessageQueue\Bulk\ExchangeInterface;
use Magento\Framework\MessageQueue\ConnectionTypeResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for ExchangeFactory.
 */
class ExchangeFactoryTest extends TestCase
{
    /**
     * @var ConnectionTypeResolver|MockObject
     */
    private $connectionTypeResolver;

    /**
     * @var ExchangeInterface|MockObject
     */
    private $amqpExchangeFactory;

    /**
     * @var ExchangeFactory
     */
    private $exchangeFactory;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->connectionTypeResolver = $this
            ->getMockBuilder(ConnectionTypeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->amqpExchangeFactory = $this
            ->getMockBuilder(\Magento\Framework\Amqp\ExchangeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->exchangeFactory = $objectManager->getObject(
            ExchangeFactory::class,
            [
                'connectionTypeResolver' => $this->connectionTypeResolver,
                'exchangeFactories' => ['amqp' => $this->amqpExchangeFactory],
            ]
        );
    }

    /**
     * Test for create method.
     *
     * @return void
     */
    public function testCreate()
    {
        $connectionName = 'amqp';
        $data = ['key1' => 'value1'];
        $this->connectionTypeResolver->expects($this->once())
            ->method('getConnectionType')->with($connectionName)->willReturn($connectionName);
        $exchange = $this
            ->getMockBuilder(Exchange::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->amqpExchangeFactory->expects($this->once())
            ->method('create')->with($connectionName, $data)->willReturn($exchange);
        $this->assertEquals($exchange, $this->exchangeFactory->create($connectionName, $data));
    }

    /**
     * Test for create method with undefined connection type.
     *
     * @return void
     */
    public function testCreateWithUndefinedConnectionType()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Not found exchange for connection name \'db\' in config');
        $connectionName = 'db';
        $data = ['key1' => 'value1'];
        $this->connectionTypeResolver->expects($this->once())
            ->method('getConnectionType')->with($connectionName)->willReturn($connectionName);
        $this->amqpExchangeFactory->expects($this->never())->method('create');
        $this->exchangeFactory->create($connectionName, $data);
    }

    /**
     * Test for create method with wrong exchange type.
     *
     * @return void
     */
    public function testCreateWithWrongExchangeType()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Exchange for connection name \'amqp\' does not implement interface');
        $connectionName = 'amqp';
        $data = ['key1' => 'value1'];
        $this->connectionTypeResolver->expects($this->once())
            ->method('getConnectionType')->with($connectionName)->willReturn($connectionName);
        $exchange = $this
            ->getMockBuilder(\Magento\Framework\Amqp\Exchange::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->amqpExchangeFactory->expects($this->once())
            ->method('create')->with($connectionName, $data)->willReturn($exchange);
        $this->exchangeFactory->create($connectionName, $data);
    }
}

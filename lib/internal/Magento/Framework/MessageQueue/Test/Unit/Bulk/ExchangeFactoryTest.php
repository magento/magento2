<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit\Bulk;

/**
 * Unit test for ExchangeFactory.
 */
class ExchangeFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\ConnectionTypeResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionTypeResolver;

    /**
     * @var \Magento\Framework\MessageQueue\Bulk\ExchangeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $amqpExchangeFactory;

    /**
     * @var \Magento\Framework\MessageQueue\Bulk\ExchangeFactory
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
            ->getMockBuilder(\Magento\Framework\MessageQueue\ConnectionTypeResolver::class)
            ->disableOriginalConstructor()->getMock();

        $this->amqpExchangeFactory = $this
            ->getMockBuilder(\Magento\Framework\Amqp\ExchangeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->exchangeFactory = $objectManager->getObject(
            \Magento\Framework\MessageQueue\Bulk\ExchangeFactory::class,
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
            ->getMockBuilder(\Magento\Framework\Amqp\Bulk\Exchange::class)
            ->disableOriginalConstructor()->getMock();
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
        $this->expectException(\LogicException::class);
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
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Exchange for connection name \'amqp\' does not implement interface');

        $connectionName = 'amqp';
        $data = ['key1' => 'value1'];
        $this->connectionTypeResolver->expects($this->once())
            ->method('getConnectionType')->with($connectionName)->willReturn($connectionName);
        $exchange = $this
            ->getMockBuilder(\Magento\Framework\Amqp\Exchange::class)
            ->disableOriginalConstructor()->getMock();
        $this->amqpExchangeFactory->expects($this->once())
            ->method('create')->with($connectionName, $data)->willReturn($exchange);
        $this->exchangeFactory->create($connectionName, $data);
    }
}

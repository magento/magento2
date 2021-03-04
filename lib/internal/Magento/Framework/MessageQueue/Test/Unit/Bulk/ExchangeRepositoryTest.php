<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit\Bulk;

/**
 * Unit test for ExchangeRepository.
 */
class ExchangeRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\Bulk\ExchangeFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $exchangeFactory;

    /**
     * @var \Magento\Framework\MessageQueue\Bulk\ExchangeRepository
     */
    private $exchangeRepository;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->exchangeFactory = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\Bulk\ExchangeFactoryInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->exchangeRepository = $objectManager->getObject(
            \Magento\Framework\MessageQueue\Bulk\ExchangeRepository::class
        );
        $objectManager->setBackwardCompatibleProperty(
            $this->exchangeRepository,
            'exchangeFactory',
            $this->exchangeFactory
        );
    }

    /**
     * Test for getByConnectionName method.
     *
     * @return void
     */
    public function testGetByConnectionName()
    {
        $connectionName = 'amqp';
        $exchange = $this
            ->getMockBuilder(\Magento\Framework\Amqp\Bulk\Exchange::class)
            ->disableOriginalConstructor()->getMock();
        $this->exchangeFactory->expects($this->once())->method('create')->with($connectionName)->willReturn($exchange);
        $this->assertEquals($exchange, $this->exchangeRepository->getByConnectionName($connectionName));
    }
}

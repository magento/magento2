<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Bulk;

use Magento\Framework\Amqp\Bulk\Exchange;
use Magento\Framework\MessageQueue\Bulk\ExchangeFactoryInterface;
use Magento\Framework\MessageQueue\Bulk\ExchangeRepository;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for ExchangeRepository.
 */
class ExchangeRepositoryTest extends TestCase
{
    /**
     * @var ExchangeFactoryInterface|MockObject
     */
    private $exchangeFactory;

    /**
     * @var ExchangeRepository
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
            ->getMockBuilder(ExchangeFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->exchangeRepository = $objectManager->getObject(
            ExchangeRepository::class
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
            ->getMockBuilder(Exchange::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->exchangeFactory->expects($this->once())->method('create')->with($connectionName)->willReturn($exchange);
        $this->assertEquals($exchange, $this->exchangeRepository->getByConnectionName($connectionName));
    }
}

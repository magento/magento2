<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Publisher\Config;

use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\Framework\MessageQueue\Publisher\Config\CompositeReader;
use Magento\Framework\MessageQueue\Publisher\Config\ReaderInterface;
use Magento\Framework\MessageQueue\Publisher\Config\ValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompositeReaderTest extends TestCase
{
    /**
     * @var CompositeReader
     */
    private $reader;

    /**
     * @var MockObject
     */
    private $validatorMock;

    /**
     * @var MockObject
     */
    private $readerOneMock;

    /**
     * @var MockObject
     */
    private $readerTwoMock;

    /**
     * @var MockObject
     */
    private $readerThreeMock;

    /**
     * @var MockObject
     */
    protected $defaultConfigProviderMock;

    /**
     * Initialize parameters
     */
    protected function setUp(): void
    {
        $this->validatorMock = $this->getMockForAbstractClass(ValidatorInterface::class);
        $this->readerOneMock = $this->getMockForAbstractClass(ReaderInterface::class);
        $this->readerTwoMock = $this->getMockForAbstractClass(ReaderInterface::class);
        $this->readerThreeMock = $this->getMockForAbstractClass(ReaderInterface::class);
        $this->defaultConfigProviderMock =
            $this->createMock(DefaultValueProvider::class);

        $this->reader = new CompositeReader(
            $this->validatorMock,
            $this->defaultConfigProviderMock,
            [
                'readerOne' => $this->readerOneMock,
                'readerThree' => $this->readerThreeMock,
                'readerTwo' => $this->readerTwoMock,
            ]
        );
    }

    public function testRead()
    {
        $this->defaultConfigProviderMock->expects($this->any())->method('getConnection')->willReturn('amqp');
        $this->defaultConfigProviderMock->expects($this->any())->method('getExchange')->willReturn('magento');

        $dataOne = include __DIR__ . '/../../_files/queue_publisher/reader_one.php';
        $dataTwo = include __DIR__ . '/../../_files/queue_publisher/reader_two.php';
        $expectedValidationData = include __DIR__ . '/../../_files/queue_publisher/data_to_validate.php';

        $this->readerOneMock->expects($this->once())->method('read')->with(null)->willReturn($dataOne);
        $this->readerTwoMock->expects($this->once())->method('read')->with(null)->willReturn($dataTwo);
        $this->readerThreeMock->expects($this->once())->method('read')->with(null)->willReturn([]);

        $this->validatorMock->expects($this->once())->method('validate')->with($expectedValidationData);

        $data = $this->reader->read();

        $expectedData = [
            //disabling existing connection and adding new
            'top04' => [
                'topic' => 'top04',
                'disabled' => false,
                'connection' => ['name' => 'db', 'disabled' => false, 'exchange' => 'magento2'],
            ],
            //two disabled connections are ignored
            'top05' => [
                'topic' => 'top05',
                'disabled' => false,
                'connection' => ['name' => 'amqp', 'exchange' => 'exch01', 'disabled' => false],
            ],
            //added default connection if not declared
            'top06' => [
                'topic' => 'top06',
                'disabled' => false,
                'connection' => ['name' => 'amqp', 'exchange' => 'magento', 'disabled' => false],
            ],
            //added default connection if all declared connections are disabled
            'top07' => [
                'topic' => 'top07',
                'disabled' => false,
                'connection' => ['name' => 'amqp', 'exchange' => 'magento', 'disabled' => false],
            ],
        ];

        $this->assertEquals($expectedData, $data);
    }
}

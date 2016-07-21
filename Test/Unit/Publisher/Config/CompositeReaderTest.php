<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Publisher\Config\Xml;

use Magento\Framework\MessageQueue\Publisher\Config\CompositeReader;
use Magento\Framework\MessageQueue\Publisher\Config\ValidatorInterface;
use Magento\Framework\MessageQueue\Publisher\Config\ReaderInterface;

class CompositeReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CompositeReader
     */
    private $reader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $readerOneMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $readerTwoMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $readerThreeMock;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $this->validatorMock = $this->getMock(ValidatorInterface::class);
        $this->readerOneMock = $this->getMock(ReaderInterface::class);
        $this->readerTwoMock = $this->getMock(ReaderInterface::class);
        $this->readerThreeMock = $this->getMock(ReaderInterface::class);

        $this->reader = new CompositeReader(
            $this->validatorMock,
            [
                'readerTwo' => ['sortOrder' => 20, 'reader' => $this->readerTwoMock],
                'readerOne' => ['sortOrder' => 10, 'reader' => $this->readerOneMock],
                'readerThree' => ['sortOrder' => 10, 'reader' => $this->readerThreeMock],
            ]
        );
    }

    public function testRead()
    {
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
                'connection' => ['name' => 'con02', 'disabled' => false, 'exchange' => 'magento2'],
            ],
            //two disabled connections are ignored
            'top05' => [
                'topic' => 'top05',
                'disabled' => false,
                'connection' => ['name' => 'con01', 'exchange' => 'exch01', 'disabled' => false],
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

    public function testInvalidReaderInstance()
    {
        $this->setExpectedException(
            '\InvalidArgumentException',
            'Reader [0] must implement Magento\Framework\MessageQueue\Publisher\Config\ReaderInterface'
        );
        $readerMock = $this->getMock(\Magento\Framework\Config\ReaderInterface::class);
        new CompositeReader($this->validatorMock, ['reader' => ['sortOrder' => 20, 'reader' => $readerMock]]);
    }
}

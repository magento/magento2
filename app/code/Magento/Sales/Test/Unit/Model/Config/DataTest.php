<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Config;

use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Config\Data;
use Magento\Sales\Model\Config\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var MockObject
     */
    private $_readerMock;

    /**
     * @var MockObject
     */
    private $_cacheMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->_readerMock = $this->getMockBuilder(
            Reader::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_cacheMock = $this->getMockBuilder(
            Config::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
    }

    public function testGet()
    {
        $expected = ['someData' => ['someValue', 'someKey' => 'someValue']];
        $this->_cacheMock->expects($this->once())
            ->method('load');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($expected);

        $configData = $this->objectManager->getObject(
            Data::class,
            [
                'reader' => $this->_readerMock,
                'cache' => $this->_cacheMock,
                'serializer' => $this->serializerMock,
            ]
        );

        $this->assertEquals($expected, $configData->get());
    }
}

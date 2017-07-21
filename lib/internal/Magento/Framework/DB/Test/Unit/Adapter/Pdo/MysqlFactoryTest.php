<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit\Adapter\Pdo;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Adapter\Pdo\MysqlFactory;
use Magento\Framework\DB\LoggerInterface;
use Magento\Framework\DB\SelectFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class MysqlFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SelectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectFactoryMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var MysqlFactory
     */
    private $mysqlFactory;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMock(ObjectManagerInterface::class);
        $this->mysqlFactory = $objectManager->getObject(
            MysqlFactory::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    /**
     * @param array $objectManagerArguments
     * @param array $config
     * @param LoggerInterface|null $logger
     * @param SelectFactory|null $selectFactory
     * @dataProvider createDataProvider
     */
    public function testCreate(
        array $objectManagerArguments,
        array $config,
        LoggerInterface $logger = null,
        SelectFactory $selectFactory = null
    ) {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                Mysql::class,
                $objectManagerArguments
            );
        $this->mysqlFactory->create(
            Mysql::class,
            $config,
            $logger,
            $selectFactory
        );
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->selectFactoryMock = $this->getMock(SelectFactory::class, [], [], '', false);
        return [
            [
                [
                    'config' => ['foo' => 'bar'],
                    'logger' => $this->loggerMock,
                    'selectFactory' => $this->selectFactoryMock
                ],
                ['foo' => 'bar'],
                $this->loggerMock,
                $this->selectFactoryMock
            ],
            [
                [
                    'config' => ['foo' => 'bar'],
                    'logger' => $this->loggerMock
                ],
                ['foo' => 'bar'],
                $this->loggerMock,
                null
            ],
            [
                [
                    'config' => ['foo' => 'bar'],
                    'selectFactory' => $this->selectFactoryMock
                ],
                ['foo' => 'bar'],
                null,
                $this->selectFactoryMock
            ],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid class, stdClass must extend Magento\Framework\DB\Adapter\Pdo\Mysql.
     */
    public function testCreateInvalidClass()
    {
        $this->mysqlFactory->create(
            \stdClass::class,
            []
        );
    }
}

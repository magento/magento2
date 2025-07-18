<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Adapter\Pdo;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Adapter\Pdo\MysqlFactory;
use Magento\Framework\DB\LoggerInterface;
use Magento\Framework\DB\SelectFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MysqlFactoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var MysqlFactory
     */
    private $mysqlFactory;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
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
     * @param string|null $loggerMockPlaceholder
     * @param string|null $selectFactoryMockPlaceholder
     * @dataProvider createDataProvider
     */
    public function testCreate(
        array $objectManagerArguments,
        array $config,
        ?string $loggerMockPlaceholder = null,
        ?string $selectFactoryMockPlaceholder = null
    ) {
        $loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $selectFactoryMock = $this->createMock(SelectFactory::class);
        if ($loggerMockPlaceholder === 'loggerMock') {
            $objectManagerArguments['logger'] = $loggerMock;
        }
        if ($selectFactoryMockPlaceholder === 'selectFactoryMock') {
            $objectManagerArguments['selectFactory'] = $selectFactoryMock;
        }
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                Mysql::class,
                $objectManagerArguments
            );
        $this->mysqlFactory->create(
            Mysql::class,
            $config,
            $loggerMockPlaceholder === 'loggerMock' ? $loggerMock : null,
            $selectFactoryMockPlaceholder === 'selectFactoryMock' ? $selectFactoryMock : null
        );
    }

    /**
     * @return array
     */
    public static function createDataProvider()
    {
        return [
            [
                [
                    'config' => ['foo' => 'bar'],
                    'logger' => 'loggerMock',
                    'selectFactory' => 'selectFactoryMock'
                ],
                ['foo' => 'bar'],
                'loggerMock',
                'selectFactoryMock'
            ],
            [
                [
                    'config' => ['foo' => 'bar'],
                    'logger' => 'loggerMock'
                ],
                ['foo' => 'bar'],
                'loggerMock',
                null
            ],
            [
                [
                    'config' => ['foo' => 'bar'],
                    'selectFactory' => 'selectFactoryMock'
                ],
                ['foo' => 'bar'],
                null,
                'selectFactoryMock'
            ],
        ];
    }

    public function testCreateInvalidClass()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid class, stdClass must extend Magento\Framework\DB\Adapter\Pdo\Mysql.');
        $this->mysqlFactory->create(
            \stdClass::class,
            []
        );
    }
}

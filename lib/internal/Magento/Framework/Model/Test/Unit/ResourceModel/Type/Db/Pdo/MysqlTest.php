<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ResourceModel\Type\Db\Pdo;

use Magento\Framework\Model\ResourceModel\Type\Db\Pdo\Mysql;

class MysqlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializerMock;

    /**
     * @var \Magento\Framework\DB\SelectFactory
     */
    private $selectFactoryMock;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\MysqlFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mysqlFactoryMock;

    protected function setUp(): void
    {
        $this->serializerMock = $this->createMock(\Magento\Framework\Serialize\SerializerInterface::class);
        $this->selectFactoryMock = $this->createMock(\Magento\Framework\DB\SelectFactory::class);
        $this->mysqlFactoryMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\MysqlFactory::class);
    }

    /**
     * @param array $inputConfig
     * @param array $expectedConfig
     * @dataProvider constructorDataProvider
     * @SuppressWarnings(PHPMD)
     */
    public function testConstructor(array $inputConfig, array $expectedConfig)
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $object = new Mysql(
            $inputConfig,
            $this->mysqlFactoryMock
        );
        //$this->assertAttributeEquals($expectedConfig, 'connectionConfig', $object);
    }

    /**
     * @return array
     */
    public function constructorDataProvider()
    {
        return [
            'default values' => [
                ['host' => 'localhost'],
                ['host' => 'localhost', 'initStatements' => 'SET NAMES utf8', 'type' => 'pdo_mysql', 'active' => false],
            ],
            'custom values' => [
                ['host' => 'localhost', 'initStatements' => 'init statement', 'type' => 'type', 'active' => true],
                ['host' => 'localhost', 'initStatements' => 'init statement', 'type' => 'type', 'active' => true],
            ],
            'active string true' => [
                ['host' => 'localhost', 'active' => 'true'],
                ['host' => 'localhost', 'initStatements' => 'SET NAMES utf8', 'type' => 'pdo_mysql', 'active' => true],
            ],
            'non-active string false' => [
                ['host' => 'localhost', 'active' => 'false'],
                ['host' => 'localhost', 'initStatements' => 'SET NAMES utf8', 'type' => 'pdo_mysql', 'active' => false],
            ],
            'non-active string 0' => [
                ['host' => 'localhost', 'active' => '0'],
                ['host' => 'localhost', 'initStatements' => 'SET NAMES utf8', 'type' => 'pdo_mysql', 'active' => false],
            ],
            'non-active bool false' => [
                ['host' => 'localhost', 'active' => false],
                ['host' => 'localhost', 'initStatements' => 'SET NAMES utf8', 'type' => 'pdo_mysql', 'active' => false],
            ],
        ];
    }

    /**
     */
    public function testConstructorException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('MySQL adapter: Missing required configuration option \'host\'');

        new Mysql(
            [],
            $this->mysqlFactoryMock
        );
    }

    /**
     */
    public function testGetConnectionInactive()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Configuration array must have a key for \'dbname\' that names the database instance'
        );

        $config = ['host' => 'localhost', 'active' => false];
        $this->mysqlFactoryMock->expects($this->once())
            ->method('create')
            ->willThrowException(
                new \InvalidArgumentException(
                    'Configuration array must have a key for \'dbname\' that names the database instance'
                )
            );
        $object = new Mysql(
            $config,
            $this->mysqlFactoryMock
        );
        $loggerMock = $this->createMock(\Magento\Framework\DB\LoggerInterface::class);
        $this->assertNull($object->getConnection($loggerMock, $this->selectFactoryMock));
    }
}

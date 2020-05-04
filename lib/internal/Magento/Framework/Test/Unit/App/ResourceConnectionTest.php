<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\App;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ResourceConnection\ConfigInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactoryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResourceConnectionTest extends TestCase
{
    /**
     * @var ResourceConnection
     */
    private $unit;

    /**
     * @var ResourceConnection|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var ConnectionFactoryInterface|MockObject
     */
    private $connectionFactoryMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    protected function setUp(): void
    {
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionFactoryMock = $this->getMockBuilder(ConnectionFactoryInterface::class)
            ->getMock();

        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMock();

        $this->objectManager = (new ObjectManager($this));
        $this->unit = $this->objectManager->getObject(
            ResourceConnection::class,
            [
                'deploymentConfig' => $this->deploymentConfigMock,
                'connectionFactory' => $this->connectionFactoryMock,
                'config' => $this->configMock,
            ]
        );
    }

    public function testGetTablePrefixWithInjectedPrefix()
    {
        /** @var ResourceConnection $resourceConnection */
        $resourceConnection = $this->objectManager->getObject(
            ResourceConnection::class,
            [
                'deploymentConfig' => $this->deploymentConfigMock,
                'connectionFactory' => $this->connectionFactoryMock,
                'config' => $this->configMock,
                'tablePrefix' => 'some_prefix'
            ]
        );

        self::assertEquals($resourceConnection->getTablePrefix(), 'some_prefix');
    }

    public function testGetTablePrefix()
    {
        $this->deploymentConfigMock->expects(self::once())
            ->method('get')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX)
            ->willReturn('pref_');
        self::assertEquals('pref_', $this->unit->getTablePrefix());
    }

    public function testGetConnectionByName()
    {
        $this->deploymentConfigMock->expects(self::once())->method('get')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS . '/default')
            ->willReturn(['config']);
        $this->connectionFactoryMock->expects(self::once())->method('create')
            ->with(['config'])
            ->willReturn('connection');

        self::assertEquals('connection', $this->unit->getConnectionByName('default'));
    }

    public function testGetExistingConnectionByName()
    {
        $unit = $this->objectManager->getObject(
            ResourceConnection::class,
            [
                'deploymentConfig' => $this->deploymentConfigMock,
                'connections' => ['default_process_' . getmypid() => 'existing_connection']
            ]
        );
        $this->deploymentConfigMock->expects(self::never())->method('get');

        self::assertEquals('existing_connection', $unit->getConnectionByName('default'));
    }

    public function testCloseConnection()
    {
        $this->configMock->expects(self::once())->method('getConnectionName')->with('default');

        $this->unit->closeConnection('default');
    }
}

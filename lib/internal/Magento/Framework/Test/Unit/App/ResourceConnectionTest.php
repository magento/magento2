<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit\App;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ResourceConnection\ConfigInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactoryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ResourceConnectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResourceConnection
     */
    private $unit;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var ConnectionFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionFactoryMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    protected function setUp()
    {
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionFactoryMock = $this->getMockBuilder(ConnectionFactoryInterface::class)
            ->getMock();

        $this->configMock = $this->getMockBuilder(ConfigInterface::class)->getMock();

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

        $this->assertEquals($resourceConnection->getTablePrefix(), 'some_prefix');
    }

    public function testGetTablePrefix()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX)
            ->willReturn('pref_');
        $this->assertEquals('pref_', $this->unit->getTablePrefix());
    }

    public function testGetConnectionByName()
    {
        $this->deploymentConfigMock->expects($this->once())->method('get')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS . '/default')
            ->willReturn(['config']);
        $this->connectionFactoryMock->expects($this->once())->method('create')
            ->with(['config'])
            ->willReturn('connection');

        $this->assertEquals('connection', $this->unit->getConnectionByName('default'));
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
        $this->deploymentConfigMock->expects($this->never())->method('get');

        $this->assertEquals('existing_connection', $unit->getConnectionByName('default'));
    }

    public function testCloseConnection()
    {
        $this->configMock->expects($this->once())->method('getConnectionName')->with('default');

        $this->unit->closeConnection('default');
    }
}

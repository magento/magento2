<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Setup\Patch\Data;

use Magento\AuthorizenetAcceptjs\Setup\Patch\Data\CopyCurrentConfig;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\App\Config;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Model\ModuleContext;
use Magento\Setup\Module\DataSetup;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\TestCase;

class CopyCurrentConfigTest extends TestCase
{
    /**
     * @var \Magento\Framework\App\Config
     */
    private $scopeConfig;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    private $resourceConfig;

    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    private $encryptor;

    /**
     * @var \Magento\Setup\Module\DataSetup
     */
    private $setup;

    /**
     * @var \Magento\Setup\Model\ModuleContext
     */
    private $context;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    private $storeManager;

    /**
     * @var \Magento\Store\Model\Website
     */
    private $website;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \Magento\Framework\DB\Select
     */
    private $select;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(Config::class);
        $this->resourceConfig = $this->createMock(ResourceConfig::class);
        $this->encryptor = $this->createMock(Encryptor::class);
        $this->setup = $this->createMock(DataSetup::class);

        $this->setup->expects($this->once())
            ->method('startSetup')
            ->willReturn(null);

        $this->setup->expects($this->once())
            ->method('endSetup')
            ->willReturn(null);

        $this->context = $this->createMock(ModuleContext::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->website = $this->createMock(Website::class);
        $this->connection = $this->createMock(AdapterInterface::class);
        $this->select = $this->createMock(Select::class);//@todo check if we really need it
    }

    public function testMigrateData(): void
    {
        $this->scopeConfig->expects($this->exactly(26))
            ->method('getValue')
            ->willReturn('TestValue');

        $this->resourceConfig->expects($this->exactly(26))
            ->method('saveConfig')
            ->willReturn(null);

        $this->encryptor->expects($this->exactly(6))
            ->method('encrypt')
            ->willReturn('TestValue');

        $this->website->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->storeManager->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$this->website]);

        $this->setup->expects($this->any())
            ->method('getTable')
            ->with('core_config_data')
            ->willReturn('core_config_data');

        $this->setup->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->connection->expects($this->exactly(26))
            ->method('select')
            ->willReturn($this->select);

        $this->select->expects($this->exactly(26))
            ->method('from')
            ->with('core_config_data')
            ->willReturnSelf();

        $this->select->expects($this->any())
            ->method('where')
            ->willReturnSelf();

        $this->connection->expects($this->exactly(26))
            ->method('fetchRow')
            ->willReturn(1);

        $objectManager = new ObjectManager($this);

        $installer = $objectManager->getObject(
            CopyCurrentConfig::class,
            [
                'moduleDataSetup' => $this->setup,
                'scopeConfig' => $this->scopeConfig,
                'resourceConfig' => $this->resourceConfig,
                'encryptor' => $this->encryptor,
                'storeManager' => $this->storeManager
            ]
        );

        $installer->apply($this->context);
    }

    public function testMigrateDataWhenValuesNotInDB(): void
    {
        $this->scopeConfig->expects($this->never())
            ->method('getValue');

        $this->resourceConfig->expects($this->never())
            ->method('saveConfig');

        $this->encryptor->expects($this->never())
            ->method('encrypt');

        $this->website->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->storeManager->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$this->website]);

        $this->setup->expects($this->any())
            ->method('getTable')
            ->with('core_config_data')
            ->willReturn('core_config_data');

        $this->setup->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->connection->expects($this->exactly(26))
            ->method('select')
            ->willReturn($this->select);

        $this->select->expects($this->exactly(26))
            ->method('from')
            ->with('core_config_data')
            ->willReturnSelf();

        $this->select->expects($this->any())
            ->method('where')
            ->willReturnSelf();

        $this->connection->expects($this->exactly(26))
            ->method('fetchRow')
            ->willReturn(null);

        $objectManager = new ObjectManager($this);

        $installer = $objectManager->getObject(
            CopyCurrentConfig::class,
            [
                'moduleDataSetup' => $this->setup,
                'scopeConfig' => $this->scopeConfig,
                'resourceConfig' => $this->resourceConfig,
                'encryptor' => $this->encryptor,
                'storeManager' => $this->storeManager
            ]
        );

        $installer->apply($this->context);
    }

    public function testMigrateDataNullFields(): void
    {
        $this->scopeConfig->expects($this->exactly(13))
            ->method('getValue')
            ->will($this->onConsecutiveCalls(1, 2, 3, 4, 5, 6, 7, 8, 9, 10));

        $this->resourceConfig->expects($this->exactly(10))
            ->method('saveConfig')
            ->willReturn(null);

        $this->encryptor->expects($this->never())
            ->method('encrypt');

        $this->storeManager->expects($this->once())
            ->method('getWebsites')
            ->willReturn([]);

        $this->setup->expects($this->any())
            ->method('getTable')
            ->with('core_config_data')
            ->willReturn('core_config_data');

        $this->setup->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->connection->expects($this->exactly(13))
            ->method('select')
            ->willReturn($this->select);

        $this->select->expects($this->exactly(13))
            ->method('from')
            ->with('core_config_data')
            ->willReturnSelf();

        $this->select->expects($this->any())
            ->method('where')
            ->willReturnSelf();

        $this->connection->expects($this->exactly(13))
            ->method('fetchRow')
            ->willReturn(1);

        $objectManager = new ObjectManager($this);

        $installer = $objectManager->getObject(
            CopyCurrentConfig::class,
            [
                'moduleDataSetup' => $this->setup,
                'scopeConfig' => $this->scopeConfig,
                'resourceConfig' => $this->resourceConfig,
                'encryptor' => $this->encryptor,
                'storeManager' => $this->storeManager
            ]
        );

        $installer->apply($this->context);
    }
}

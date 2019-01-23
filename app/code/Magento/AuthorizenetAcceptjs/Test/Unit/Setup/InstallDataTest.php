<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Setup;

use Magento\AuthorizenetAcceptjs\Setup\InstallData;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\App\Config;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\DataSetup;
use Magento\Setup\Model\ModuleContext;
use PHPUnit\Framework\TestCase;

class InstallDataTest extends TestCase
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
    }

    public function testMigrateData(): void
    {
        $this->scopeConfig->expects($this->exactly(10))
            ->method('getValue')
            ->willReturn('TestValue');

        $this->resourceConfig->expects($this->exactly(10))
            ->method('saveConfig')
            ->willReturn(null);

        $this->encryptor->expects($this->exactly(3))
            ->method('encrypt')
            ->willReturn('TestValue');

        $objectManager = new ObjectManager($this);

        $installer = $objectManager->getObject(
            InstallData::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'resourceConfig' => $this->resourceConfig,
                'encryptor' => $this->encryptor
            ]
        );

        $installer->install($this->setup, $this->context);
    }

    public function testMigrateDataNullFields(): void
    {
        $this->scopeConfig->expects($this->exactly(10))
            ->method('getValue')
            ->will($this->onConsecutiveCalls(1, 2, 3, 4, 5, 6, 7));

        $this->resourceConfig->expects($this->exactly(7))
            ->method('saveConfig')
            ->willReturn(null);

        $this->encryptor->expects($this->never())
            ->method('encrypt');

        $objectManager = new ObjectManager($this);

        $installer = $objectManager->getObject(
            InstallData::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'resourceConfig' => $this->resourceConfig,
                'encryptor' => $this->encryptor
            ]
        );

        $installer->install($this->setup, $this->context);
    }
}

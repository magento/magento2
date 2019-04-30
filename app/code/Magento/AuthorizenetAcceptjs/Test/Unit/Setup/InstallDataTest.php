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
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for Magento\AuthorizenetAcceptjs\Test\Unit\Setup\InstallData
 */
class InstallDataTest extends TestCase
{
    /**
     * @var \Magento\Framework\App\Config|MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config|MockObject
     */
    private $resourceConfig;

    /**
     * @var \Magento\Framework\Encryption\Encryptor|MockObject
     */
    private $encryptor;

    /**
     * @var \Magento\Setup\Module\DataSetup|MockObject
     */
    private $setup;

    /**
     * @var \Magento\Setup\Model\ModuleContext|MockObject
     */
    private $context;

    /**
     * @var \Magento\Store\Model\StoreManager|MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Store\Model\Website|MockObject
     */
    private $website;

    /**
     * @var InstallData
     */
    private $installer;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
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

        $this->installer = $objectManager->getObject(
            InstallData::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'resourceConfig' => $this->resourceConfig,
                'encryptor' => $this->encryptor,
                'storeManager' => $this->storeManager,
            ]
        );
    }

    /**
     * @return void
     */
    public function testMigrateData()
    {
        $this->scopeConfig->expects($this->exactly(39))
            ->method('getValue')
            ->willReturn('TestValue');

        $this->resourceConfig->expects($this->exactly(13))
            ->method('saveConfig')
            ->willReturn(null);

        $this->encryptor->expects($this->exactly(3))
            ->method('encrypt')
            ->willReturn('TestValue');

        $this->website->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->storeManager->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$this->website]);

        $this->installer->install($this->setup, $this->context);
    }

    /**
     * @return void
     */
    public function testMigrateDataNullFields()
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

        $this->installer->install($this->setup, $this->context);
    }
}

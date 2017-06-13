<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Test\Unit\Cron;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SystemBackupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Backup\Cron\SystemBackup
     */
    private $systemBackup;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\Backup\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupDataMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registryMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * Filesystem facade
     *
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * @var \Magento\Framework\Backup\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupFactoryMock;

    /**
     * @var \Magento\Framework\App\MaintenanceMode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $maintenanceModeMock;

    /**
     * @var \Magento\Framework\Backup\Db|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupDbMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMock();
        $this->backupDataMock = $this->getMockBuilder(\Magento\Backup\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->getMock();
        $this->filesystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backupFactoryMock = $this->getMockBuilder(\Magento\Framework\Backup\Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->maintenanceModeMock = $this->getMockBuilder(\Magento\Framework\App\MaintenanceMode::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->backupDbMock = $this->getMockBuilder(\Magento\Framework\Backup\Db::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backupDbMock->expects($this->any())->method('setBackupExtension')->willReturnSelf();
        $this->backupDbMock->expects($this->any())->method('setTime')->willReturnSelf();
        $this->backupDbMock->expects($this->any())->method('setBackupsDir')->willReturnSelf();

        $this->objectManager = new ObjectManager($this);
        $this->systemBackup = $this->objectManager->getObject(
            \Magento\Backup\Cron\SystemBackup::class,
            [
                'backupData' => $this->backupDataMock,
                'coreRegistry' => $this->registryMock,
                'logger' => $this->loggerMock,
                'scopeConfig' => $this->scopeConfigMock,
                'filesystem' => $this->filesystemMock,
                'backupFactory' => $this->backupFactoryMock,
                'maintenanceMode' => $this->maintenanceModeMock,
            ]
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testExecuteThrowsException()
    {
        $type = 'db';
        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')->willReturn(true);

        $this->scopeConfigMock->expects($this->once())->method('getValue')
            ->with('system/backup/type', 'store')
            ->willReturn($type);

        $this->backupFactoryMock->expects($this->once())->method('create')->willReturn($this->backupDbMock);

        $this->backupDbMock->expects($this->once())->method('create')->willThrowException(new \Exception);

        $this->backupDataMock->expects($this->never())->method('getCreateSuccessMessageByType')->with($type);
        $this->loggerMock->expects($this->never())->method('info');

        $this->systemBackup->execute();
    }
}

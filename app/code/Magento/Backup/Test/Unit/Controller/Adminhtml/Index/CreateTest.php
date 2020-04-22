<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backup\Test\Unit\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Backup\Controller\Adminhtml\Index\Create;
use Magento\Backup\Model\Backup;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Backup\Factory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests \Magento\Backup\Controller\Adminhtml\Index\Create class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ResponseInterface|MockObject
     */
    private $responseMock;

    /**
     * @var Backup|MockObject
     */
    private $backupModelMock;

    /**
     * @var Data|MockObject
     */
    private $dataBackendHelperMock;

    /**
     * @var \Magento\Backup\Helper\Data|MockObject
     */
    private $dataBackupHelperMock;

    /**
     * @var FileFactory|MockObject
     */
    private $fileFactoryMock;

    /**
     * @var Session|MockObject
     */
    private $sessionMock;

    /**
     * @var MaintenanceMode|MockObject
     */
    private $maintenanceModeMock;

    /**
     * @var Factory|MockObject
     */
    private $backupFactoryMock;

    /**
     * @var Create
     */
    private $createController;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMock();
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAjax', 'isPost', 'getParam'])
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['representJson', 'setRedirect'])
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backupFactoryMock = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->backupModelMock = $this->getMockBuilder(Backup::class)
            ->disableOriginalConstructor()
            ->setMethods(['setBackupExtension', 'setTime', 'setBackupsDir', 'setName', 'create'])
            ->getMock();
        $this->dataBackendHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMock();
        $this->dataBackupHelperMock = $this->getMockBuilder(\Magento\Backup\Helper\Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionByType', 'getBackupsDir'])
            ->getMock();
        $this->maintenanceModeMock = $this->getMockBuilder(MaintenanceMode::class)
            ->disableOriginalConstructor()
            ->setMethods(['set'])
            ->getMock();
        $this->fileFactoryMock = $this->getMockBuilder(FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager = new ObjectManager($this);
        $this->context = $this->objectManager->getObject(
            Context::class,
            [
                'objectManager' => $this->objectManagerMock,
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'session' => $this->sessionMock,
                'helper' => $this->dataBackendHelperMock,
                'maintenanceMode' => $this->maintenanceModeMock,
            ]
        );
        $this->createController = $this->objectManager->getObject(
            Create::class,
            [
                'context' => $this->context,
                'backupFactory' => $this->backupFactoryMock,
                'fileFactory' => $this->fileFactoryMock,
            ]
        );
    }

    /**
     * @covers \Magento\Backup\Controller\Adminhtml\Index\Create::execute
     * @return void
     */
    public function testExecuteNotPost()
    {
        $redirectUrl = '*/*/index';
        $redirectUrlBackup = 'backup/index/index';

        $this->requestMock->expects($this->any())
            ->method('isAjax')
            ->willReturn(true);
        $this->requestMock->expects($this->any())
            ->method('isPost')
            ->willReturn(false);
        $this->dataBackendHelperMock->expects($this->any())
            ->method('getUrl')
            ->with($redirectUrl, [])
            ->willReturn($redirectUrlBackup);
        $this->responseMock->expects($this->any())
            ->method('setRedirect')
            ->with($redirectUrlBackup)
            ->willReturnSelf();

        $this->assertSame($this->responseMock, $this->createController->execute());
    }

    /**
     * @covers \Magento\Backup\Controller\Adminhtml\Index\Create::execute
     * @return void
     */
    public function testExecutePermission()
    {
        $redirectUrl = '*/*/index';
        $redirectUrlBackup = 'backup/index/index';
        $backupType = 'db';
        $backupName = 'backup1';
        $response = '{"redirect_url":"backup\/index\/index"}';

        $this->requestMock->expects($this->any())
            ->method('isAjax')
            ->willReturn(true);
        $this->requestMock->expects($this->any())
            ->method('isPost')
            ->willReturn(true);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['type', null, $backupType],
                ['backup_name', null, $backupName],
            ]);
        $this->dataBackendHelperMock->expects($this->any())
            ->method('getUrl')
            ->with($redirectUrl, [])
            ->willReturn($redirectUrlBackup);
        $this->responseMock->expects($this->any())
            ->method('representJson')
            ->with($response)
            ->willReturnSelf();
        $this->maintenanceModeMock->expects($this->any())
            ->method('set')
            ->with(true)
            ->willReturn(false);
        $this->backupFactoryMock->expects($this->any())
            ->method('create')
            ->with($backupType)
            ->willReturn($this->backupModelMock);
        $this->backupModelMock->expects($this->any())
            ->method('setBackupExtension')
            ->with($backupType)
            ->willReturnSelf();
        $this->backupModelMock->expects($this->any())
            ->method('setBackupsDir')
            ->willReturnSelf();
        $this->backupModelMock->expects($this->any())
            ->method('setTime')
            ->willReturnSelf();
        $this->backupModelMock->expects($this->any())
            ->method('setName')
            ->with($backupName)
            ->willReturnSelf();
        $this->backupModelMock->expects($this->once())
            ->method('create')
            ->willReturnSelf();
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(\Magento\Backup\Helper\Data::class)
            ->willReturn($this->dataBackupHelperMock);
        $this->dataBackupHelperMock->expects($this->any())
            ->method('getExtensionByType')
            ->with($backupType)
            ->willReturn($backupType);
        $this->dataBackupHelperMock->expects($this->any())
            ->method('getBackupsDir')
            ->willReturn('dir');

        $this->assertNull($this->createController->execute());
    }
}

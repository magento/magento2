<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Test\Unit\Controller\Adminhtml\Index;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Tests \Magento\Backup\Controller\Adminhtml\Index\Create class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    private $context;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManagerMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Backup\Model\Backup|\PHPUnit\Framework\MockObject\MockObject
     */
    private $backupModelMock;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dataBackendHelperMock;

    /**
     * @var \Magento\Backup\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dataBackupHelperMock;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fileFactoryMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sessionMock;

    /**
     * @var \Magento\Framework\App\MaintenanceMode|\PHPUnit\Framework\MockObject\MockObject
     */
    private $maintenanceModeMock;

    /**
     * @var \Magento\Framework\Backup\Factory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $backupFactoryMock;

    /**
     * @var \Magento\Backup\Controller\Adminhtml\Index\Create
     */
    private $createController;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAjax', 'isPost', 'getParam'])
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['representJson', 'setRedirect'])
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backupFactoryMock = $this->getMockBuilder(\Magento\Framework\Backup\Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->backupModelMock = $this->getMockBuilder(\Magento\Backup\Model\Backup::class)
            ->disableOriginalConstructor()
            ->setMethods(['setBackupExtension', 'setTime', 'setBackupsDir', 'setName', 'create'])
            ->getMock();
        $this->dataBackendHelperMock = $this->getMockBuilder(\Magento\Backend\Helper\Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMock();
        $this->dataBackupHelperMock = $this->getMockBuilder(\Magento\Backup\Helper\Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionByType', 'getBackupsDir'])
            ->getMock();
        $this->maintenanceModeMock = $this->getMockBuilder(\Magento\Framework\App\MaintenanceMode::class)
            ->disableOriginalConstructor()
            ->setMethods(['set'])
            ->getMock();
        $this->fileFactoryMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http\FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager = new ObjectManager($this);
        $this->context = $this->objectManager->getObject(
            \Magento\Backend\App\Action\Context::class,
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
            \Magento\Backup\Controller\Adminhtml\Index\Create::class,
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

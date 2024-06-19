<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backup\Test\Unit\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backup\Controller\Adminhtml\Index\Rollback;
use Magento\Backup\Helper\Data;
use Magento\Backup\Model\Backup;
use Magento\Backup\Model\BackupFactory;
use Magento\Backup\Model\ResourceModel\Db;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Backup\BackupInterface;
use Magento\Framework\Backup\Factory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class RollbackTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var Rollback
     */
    private $rollbackController;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ResponseInterface|MockObject
     */
    private $responseMock;

    /**
     * @var BackupFactory|MockObject
     */
    private $backupModelFactoryMock;

    /**
     * @var Backup|MockObject
     */
    private $backupModelMock;

    /**
     * @var Data|MockObject
     */
    private $dataHelperMock;

    /**
     * @var FileFactory|MockObject
     */
    private $fileFactoryMock;

    /**
     * @var Factory|MockObject
     */
    private $backupFactoryMock;

    /**
     * @var BackupInterface|MockObject
     */
    private $backupManagerMock;

    /**
     * @var Db|MockObject
     */
    private $backupResourceModelMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->addMethods(['initForward', 'setDispatched', 'isAjax'])
            ->getMockForAbstractClass();
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->addMethods(['setRedirect', 'representJson'])
            ->getMockForAbstractClass();
        $this->backupModelFactoryMock = $this->getMockBuilder(BackupFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->backupModelMock = $this->getMockBuilder(Backup::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['exists', 'getSize', 'output', 'validateUserPassword'])
            ->addMethods(['getTime'])
            ->getMock();
        $this->backupResourceModelMock = $this->getMockBuilder(Db::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isRollbackAllowed', 'getBackupsDir', 'invalidateCache'])
            ->getMock();
        $this->fileFactoryMock = $this->getMockBuilder(FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backupFactoryMock = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->backupManagerMock = $this->getMockBuilder(BackupInterface::class)
            ->addMethods(['setName'])
            ->getMockForAbstractClass();
        $this->objectManager = new ObjectManager($this);
        $this->context = $this->objectManager->getObject(
            Context::class,
            [
                'objectManager' => $this->objectManagerMock,
                'request' => $this->requestMock,
                'response' => $this->responseMock
            ]
        );
        $this->rollbackController = $this->objectManager->getObject(
            Rollback::class,
            [
                'context' => $this->context,
                'backupFactory' => $this->backupFactoryMock,
                'backupModelFactory' => $this->backupModelFactoryMock,
                'fileFactory' => $this->fileFactoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecuteRollbackDisabled(): void
    {
        $rollbackAllowed = false;

        $this->dataHelperMock->expects($this->once())
            ->method('isRollbackAllowed')
            ->willReturn($rollbackAllowed);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(Data::class)
            ->willReturn($this->dataHelperMock);

        $this->assertSame($this->responseMock, $this->rollbackController->execute());
    }

    /**
     * @return void
     */
    public function testExecuteBackupNotFound(): void
    {
        $rollbackAllowed = true;
        $isAjax = true;
        $time = 0;
        $type = 'db';
        $exists = false;

        $this->dataHelperMock->expects($this->once())
            ->method('isRollbackAllowed')
            ->willReturn($rollbackAllowed);
        $this->objectManagerMock->expects($this->atLeastOnce())
            ->method('get')
            ->with(Data::class)
            ->willReturn($this->dataHelperMock);
        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn($isAjax);
        $this->backupModelMock->expects($this->atLeastOnce())
            ->method('getTime')
            ->willReturn($time);
        $this->backupModelMock->expects($this->any())
            ->method('exists')
            ->willReturn($exists);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['time', null, $time],
                    ['type', null, $type]
                ]
            );
        $this->backupModelFactoryMock->expects($this->once())
            ->method('create')
            ->with($time, $type)
            ->willReturn($this->backupModelMock);

        $this->assertSame($this->responseMock, $this->rollbackController->execute());
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $rollbackAllowed = true;
        $isAjax = true;
        $time = 1;
        $type = 'db';
        $exists = true;
        $passwordValid = true;

        $this->dataHelperMock->expects($this->once())
            ->method('isRollbackAllowed')
            ->willReturn($rollbackAllowed);
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(Data::class)
            ->willReturn($this->dataHelperMock);
        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn($isAjax);
        $this->backupModelMock->expects($this->atLeastOnce())
            ->method('getTime')
            ->willReturn($time);
        $this->backupModelMock->expects($this->any())
            ->method('exists')
            ->willReturn($exists);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['time', null, $time],
                    ['type', null, $type],
                ]
            );
        $this->backupModelFactoryMock->expects($this->once())
            ->method('create')
            ->with($time, $type)
            ->willReturn($this->backupModelMock);
        $this->backupManagerMock->expects($this->once())
            ->method('setBackupExtension')
            ->willReturn($this->backupManagerMock);
        $this->backupManagerMock->expects($this->once())
            ->method('setTime')
            ->willReturn($this->backupManagerMock);
        $this->backupManagerMock->expects($this->once())
            ->method('setBackupsDir')
            ->willReturn($this->backupManagerMock);
        $this->backupManagerMock->expects($this->once())
            ->method('setName')
            ->willReturn($this->backupManagerMock);
        $this->backupManagerMock->expects($this->once())
            ->method('setResourceModel')
            ->willReturn($this->backupManagerMock);
        $this->backupFactoryMock->expects($this->once())
            ->method('create')
            ->with($type)
            ->willReturn($this->backupManagerMock);
        $this->objectManagerMock
            ->method('create')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1 == Db::class && empty($arg2)) {
                    return $this->backupResourceModelMock;
                } elseif ($arg1 == Backup::class && empty($arg2)) {
                    return $this->backupModelMock;
                }
            });
        $this->backupModelMock->expects($this->once())
            ->method('validateUserPassword')
            ->willReturn($passwordValid);

        $this->rollbackController->execute();
    }
}

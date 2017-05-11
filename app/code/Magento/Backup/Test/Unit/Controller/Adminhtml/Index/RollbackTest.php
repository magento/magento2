<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Test\Unit\Controller\Adminhtml\Index;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class RollbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    private $context;

    /**
     * @var \Magento\Backup\Controller\Adminhtml\Index\Rollback
     */
    private $rollbackController;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Backup\Model\BackupFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupModelFactoryMock;

    /**
     * @var \Magento\Backup\Model\Backup|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupModelMock;

    /**
     * @var \Magento\Backup\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataHelperMock;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultForwardMock;

    /**
     * @var \Magento\Framework\Backup\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupFactoryMock;

    /**
     * @var \Magento\Framework\Backup\BackupInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupManagerMock;

    /**
     * @var \Magento\Backup\Model\ResourceModel\Db|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupResourceModelMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['initForward', 'setDispatched', 'isAjax'])
            ->getMockForAbstractClass();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->setMethods(['setRedirect', 'representJson'])
            ->getMockForAbstractClass();
        $this->backupModelFactoryMock = $this->getMockBuilder(\Magento\Backup\Model\BackupFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->backupModelMock = $this->getMockBuilder(\Magento\Backup\Model\Backup::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTime', 'exists', 'getSize', 'output', 'validateUserPassword'])
            ->getMock();
        $this->backupResourceModelMock = $this->getMockBuilder(\Magento\Backup\Model\ResourceModel\Db::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataHelperMock = $this->getMockBuilder(\Magento\Backup\Helper\Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['isRollbackAllowed', 'getBackupsDir', 'invalidateCache'])
            ->getMock();
        $this->fileFactoryMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http\FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock =
            $this->getMockBuilder(\Magento\Backend\Model\View\Result\RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backupFactoryMock = $this->getMockBuilder(\Magento\Framework\Backup\Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->backupManagerMock = $this->getMockBuilder(\Magento\Framework\Backup\BackupInterface::class)
            ->setMethods(['setName'])
            ->getMockForAbstractClass();
        $this->objectManager = new ObjectManager($this);
        $this->context = $this->objectManager->getObject(
            \Magento\Backend\App\Action\Context::class,
            [
                'objectManager' => $this->objectManagerMock,
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
            ]
        );
        $this->rollbackController = $this->objectManager->getObject(
            \Magento\Backup\Controller\Adminhtml\Index\Rollback::class,
            [
                'context' => $this->context,
                'backupFactory' => $this->backupFactoryMock,
                'backupModelFactory' => $this->backupModelFactoryMock,
                'fileFactory' => $this->fileFactoryMock,
            ]
        );
    }

    public function testExecuteRollbackDisabled()
    {
        $rollbackAllowed = false;

        $this->dataHelperMock->expects($this->once())
            ->method('isRollbackAllowed')
            ->willReturn($rollbackAllowed);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Backup\Helper\Data::class)
            ->willReturn($this->dataHelperMock);

        $this->assertSame($this->responseMock, $this->rollbackController->execute());
    }

    public function testExecuteBackupNotFound()
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
            ->with(\Magento\Backup\Helper\Data::class)
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

        $this->assertSame($this->responseMock, $this->rollbackController->execute());
    }

    public function testExecute()
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
            ->with(\Magento\Backup\Helper\Data::class)
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
        $this->objectManagerMock->expects($this->at(2))
            ->method('create')
            ->with(\Magento\Backup\Model\ResourceModel\Db::class, [])
            ->willReturn($this->backupResourceModelMock);
        $this->objectManagerMock->expects($this->at(3))
            ->method('create')
            ->with(\Magento\Backup\Model\Backup::class, [])
            ->willReturn($this->backupModelMock);
        $this->backupModelMock->expects($this->once())
            ->method('validateUserPassword')
            ->willReturn($passwordValid);

        $this->rollbackController->execute();
    }
}

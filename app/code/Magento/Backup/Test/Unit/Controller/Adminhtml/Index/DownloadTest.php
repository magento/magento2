<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Test\Unit\Controller\Adminhtml\Index;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @covers \Magento\Backup\Controller\Adminhtml\Index\Download
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Backup\Controller\Adminhtml\Index\Download
     */
    protected $downloadController;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Backup\Model\BackupFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backupModelFactoryMock;

    /**
     * @var \Magento\Backup\Model\Backup|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backupModelMock;

    /**
     * @var \Magento\Backup\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataHelperMock;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRawFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Raw|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRawMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->getMock();
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\ResponseInterface')
            ->getMock();
        $this->backupModelFactoryMock = $this->getMockBuilder('Magento\Backup\Model\BackupFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->backupModelMock = $this->getMockBuilder('Magento\Backup\Model\Backup')
            ->disableOriginalConstructor()
            ->setMethods(['getTime', 'exists', 'getSize', 'output'])
            ->getMock();
        $this->dataHelperMock = $this->getMockBuilder('Magento\Backup\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileFactoryMock = $this->getMockBuilder('Magento\Framework\App\Response\Http\FileFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRawFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\Result\RawFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirectFactoryMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRawMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Raw')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->context = $this->objectManager->getObject(
            'Magento\Backend\App\Action\Context',
            [
                'objectManager' => $this->objectManagerMock,
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock
            ]
        );
        $this->downloadController = $this->objectManager->getObject(
            'Magento\Backup\Controller\Adminhtml\Index\Download',
            [
                'context' => $this->context,
                'backupModelFactory' => $this->backupModelFactoryMock,
                'fileFactory' => $this->fileFactoryMock,
                'resultRawFactory' => $this->resultRawFactoryMock,
            ]
        );
    }

    /**
     * @covers \Magento\Backup\Controller\Adminhtml\Index\Download::execute
     */
    public function testExecuteBackupFound()
    {
        $time = 1;
        $type = 'db';
        $filename = 'filename';
        $size = 10;
        $output = 'test';

        $this->backupModelMock->expects($this->atLeastOnce())
            ->method('getTime')
            ->willReturn($time);
        $this->backupModelMock->expects($this->atLeastOnce())
            ->method('exists')
            ->willReturn(true);
        $this->backupModelMock->expects($this->atLeastOnce())
            ->method('getSize')
            ->willReturn($size);
        $this->backupModelMock->expects($this->atLeastOnce())
            ->method('output')
            ->willReturn($output);
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
        $this->dataHelperMock->expects($this->once())
            ->method('generateBackupDownloadName')
            ->with($this->backupModelMock)
            ->willReturn($filename);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Backup\Helper\Data')
            ->willReturn($this->dataHelperMock);
        $this->fileFactoryMock->expects($this->once())
            ->method('create')->with(
                $filename,
                null,
                DirectoryList::VAR_DIR,
                'application/octet-stream',
                $size
            )
            ->willReturn($this->responseMock);
        $this->resultRawMock->expects($this->once())
            ->method('setContents')
            ->with($output);
        $this->resultRawFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRawMock);

        $this->assertSame($this->resultRawMock, $this->downloadController->execute());
    }

    /**
     * @covers \Magento\Backup\Controller\Adminhtml\Index\Download::execute
     * @param int $time
     * @param bool $exists
     * @param int $existsCount
     * @dataProvider executeBackupNotFoundDataProvider
     */
    public function testExecuteBackupNotFound($time, $exists, $existsCount)
    {
        $type = 'db';

        $this->backupModelMock->expects($this->atLeastOnce())
            ->method('getTime')
            ->willReturn($time);
        $this->backupModelMock->expects($this->exactly($existsCount))
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
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('backup/*');
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->assertSame($this->resultRedirectMock, $this->downloadController->execute());
    }

    /**
     * @return array
     */
    public function executeBackupNotFoundDataProvider()
    {
        return [
            [1, false, 1],
            [0, true, 0],
            [0, false, 0]
        ];
    }
}

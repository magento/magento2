<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Controller\Adminhtml\Index;

class DownloadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Backup\Model\BackupFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backupModelFactory;

    /**
     * @var \Magento\Backup\Model\Backup|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backup;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    public function setUp()
    {
        $this->backup = $this->getMock(
            '\Magento\Backup\Model\Backup',
            ['getTime', 'exists', 'getSize', 'output'],
            [],
            '',
            false
        );
        $this->request = $this->getMock('\Magento\Framework\App\RequestInterface', [], [], '', false);
        $this->backupModelFactory = $this->getMock('\Magento\Backup\Model\BackupFactory', [], [], '', false);
        $this->response = $this->getMock('\Magento\Framework\App\ResponseInterface', [], [], '', false);
    }

    public function testExecuteBackupFound()
    {
        $time = 1;
        $type = 'db';
        $filename = 'filename';
        $size = 10;
        $output = 'test';

        $this->backup->expects($this->once())->method('getTime')->willReturn($time);
        $this->backup->expects($this->once())->method('exists')->willReturn(true);
        $this->backup->expects($this->once())->method('getSize')->willReturn($size);
        $this->backup->expects($this->once())->method('output')->willReturn($output);

        $this->request->expects($this->at(0))->method('getParam')->with('time')->willReturn($time);
        $this->request->expects($this->at(1))->method('getParam')->with('type')->willReturn($type);

        $this->backupModelFactory->expects($this->once())->method('create')->with($time, $type)
            ->willReturn($this->backup);

        $helper = $this->getMock('Magento\Backup\Helper\Data', [], [], '', false);
        $helper->expects($this->once())->method('generateBackupDownloadName')->with($this->backup)
            ->willReturn($filename);

        $objectManager = $this->getMock('\Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $objectManager->expects($this->once())->method('get')->with('Magento\Backup\Helper\Data')
            ->willReturn($helper);

        $fileFactory = $this->getMock('\Magento\Framework\App\Response\Http\FileFactory', [], [], '', false);
        $fileFactory->expects($this->once())->method('create')->with(
            $filename,
            null,
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
            'application/octet-stream',
            $size
        )->willReturn($this->response);

        $resultRaw = $this->getMock('\Magento\Framework\Controller\Result\Raw', [], [], '', false);
        $resultRaw->expects($this->once())->method('setContents')->with($output);

        $resultRawFactory = $this->getMock(
            '\Magento\Framework\Controller\Result\RawFactory',
            ['create'],
            [],
            '',
            false
        );
        $resultRawFactory->expects($this->once())->method('create')->willReturn($resultRaw);

        $context = $this->getMock('\Magento\Backend\App\Action\Context', [], [], '', false);
        $context->expects($this->once())->method('getRequest')->willReturn($this->request);
        $context->expects($this->once())->method('getObjectManager')->willReturn($objectManager);
        $context->expects($this->once())->method('getResponse')->willReturn($this->response);

        /** @var Download|\PHPUnit_Framework_MockObject_MockObject $controller */
        $controller = (new \Magento\TestFramework\Helper\ObjectManager($this))->getObject(
            'Magento\Backup\Controller\Adminhtml\Index\Download',
            [
                'backupModelFactory' => $this->backupModelFactory,
                'context' => $context,
                'fileFactory' => $fileFactory,
                'resultRawFactory' => $resultRawFactory
            ]
        );
        $this->assertSame($resultRaw, $controller->execute());
    }

    /**
     * @dataProvider executeBackupNotFoundDataProvider
     * @param string $time
     * @param bool $exists
     * @param int $existsCount
     */
    public function testExecuteBackupNotFound($time, $exists, $existsCount)
    {
        $type = 'db';

        $this->backup->expects($this->once())->method('getTime')->willReturn($time);
        $this->backup->expects($this->exactly($existsCount))->method('exists')->willReturn($exists);

        $this->request->expects($this->any())->method('getParam')
            ->will($this->returnValueMap([['time', null, $time], ['type', null, $type]]));

        $context = $this->getMock('\Magento\Backend\App\Action\Context', [], [], '', false);
        $context->expects($this->once())->method('getRequest')->willReturn($this->request);
        $context->expects($this->once())->method('getResponse')->willReturn($this->response);

        $this->backupModelFactory->expects($this->once())->method('create')->with($time, $type)
            ->willReturn($this->backup);

        $resultRedirect = $this->getMock('Magento\Backend\Model\View\Result\Redirect', [], [], '', false);
        $resultRedirect->expects($this->once())->method('setPath')->with('backup/*');

        $resultRedirectFactory = $this->getMock(
            'Magento\Backend\Model\View\Result\RedirectFactory',
            ['create'],
            [],
            '',
            false
        );
        $resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);

        /** @var Download|\PHPUnit_Framework_MockObject_MockObject $controller */
        $controller = (new \Magento\TestFramework\Helper\ObjectManager($this))->getObject(
            'Magento\Backup\Controller\Adminhtml\Index\Download',
            [
                'context' => $context,
                'backupModelFactory' => $this->backupModelFactory,
                'resultRedirectFactory' => $resultRedirectFactory
            ]
        );
        $this->assertSame($resultRedirect, $controller->execute());
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
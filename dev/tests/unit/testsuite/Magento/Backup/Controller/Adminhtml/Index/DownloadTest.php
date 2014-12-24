<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backup\Controller\Adminhtml\Index;

class DownloadTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteBackupFound()
    {
        $time = 1;
        $type = 'db';
        $filename = 'filename';
        $size = 10;
        $output = 'test';

        $backup = $this->getMock(
            '\Magento\Backup\Model\Backup',
            ['getTime', 'exists', 'getSize', 'output'],
            [],
            '',
            false
        );
        $backup->expects($this->once())->method('getTime')->will($this->returnValue($time));
        $backup->expects($this->once())->method('exists')->will($this->returnValue(true));
        $backup->expects($this->once())->method('getSize')->will($this->returnValue($size));
        $backup->expects($this->once())->method('output')->will($this->returnValue($output));

        $request = $this->getMock('\Magento\Framework\App\RequestInterface', [], [], '', false);
        $request->expects($this->at(0))->method('getParam')->with('time')->will($this->returnValue($time));
        $request->expects($this->at(1))->method('getParam')->with('type')->will($this->returnValue($type));

        $backupModelFactory = $this->getMock('\Magento\Backup\Model\BackupFactory', [], [], '', false);
        $backupModelFactory->expects($this->once())->method('create')->with($time, $type)
            ->will($this->returnValue($backup));

        $helper = $this->getMock('Magento\Backup\Helper\Data', [], [], '', false);
        $helper->expects($this->once())->method('generateBackupDownloadName')->with($backup)
            ->will($this->returnValue($filename));

        $objectManager = $this->getMock('\Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $objectManager->expects($this->once())->method('get')->with('Magento\Backup\Helper\Data')
            ->will($this->returnValue($helper));

        $response = $this->getMock('\Magento\Framework\App\ResponseInterface', [], [], '', false);

        $fileFactory = $this->getMock('\Magento\Framework\App\Response\Http\FileFactory', [], [], '', false);
        $fileFactory->expects($this->once())->method('create')->with(
            $filename,
            null,
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
            'application/octet-stream',
            $size
        )->will($this->returnValue($response));

        $resultRaw = $this->getMock('\Magento\Framework\Controller\Result\Raw', [], [], '', false);
        $resultRaw->expects($this->once())->method('setContents')->with($output);

        $resultRawFactory = $this->getMock('\Magento\Framework\Controller\Result\RawFactory', [], [], '', false);
        $resultRawFactory->expects($this->once())->method('create')->will($this->returnValue($resultRaw));

        $context = $this->getMock('\Magento\Backend\App\Action\Context', [], [], '', false);
        $context->expects($this->once())->method('getRequest')->will($this->returnValue($request));
        $context->expects($this->once())->method('getObjectManager')->will($this->returnValue($objectManager));
        $context->expects($this->once())->method('getResponse')->will($this->returnValue($response));

        /** @var Download|\PHPUnit_Framework_MockObject_MockObject $controller */
        $controller = (new \Magento\TestFramework\Helper\ObjectManager($this))->getObject(
            'Magento\Backup\Controller\Adminhtml\Index\Download',
            [
                'backupModelFactory' => $backupModelFactory,
                'context' => $context,
                'fileFactory' => $fileFactory,
                'resultRawFactory' => $resultRawFactory
            ]
        );
        $this->assertSame($resultRaw, $controller->execute());
    }

    /**
     * @dataProvider providerExecuteBackupNotFound
     * @param string $time
     * @param bool $exists
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $existsCount
     */
    public function testExecuteBackupNotFound($time, $exists, $existsCount)
    {
        $type = 'db';

        $backup = $this->getMock('\Magento\Backup\Model\Backup', ['getTime', 'exists'], [], '', false);
        $backup->expects($this->once())->method('getTime')->will($this->returnValue($time));
        $backup->expects($existsCount)->method('exists')->will($this->returnValue($exists));

        $response = $this->getMock('\Magento\Framework\App\ResponseInterface', [], [], '', false);

        $request = $this->getMock('\Magento\Framework\App\RequestInterface', [], [], '', false);
        $request->expects($this->at(0))->method('getParam')->with('time')->will($this->returnValue($time));
        $request->expects($this->at(1))->method('getParam')->with('type')->will($this->returnValue($type));

        $context = $this->getMock('\Magento\Backend\App\Action\Context', [], [], '', false);
        $context->expects($this->once())->method('getRequest')->will($this->returnValue($request));
        $context->expects($this->once())->method('getResponse')->will($this->returnValue($response));

        $backupModelFactory = $this->getMock('\Magento\Backup\Model\BackupFactory', [], [], '', false);
        $backupModelFactory->expects($this->once())->method('create')->with($time, $type)
            ->will($this->returnValue($backup));

        $resultRedirect = $this->getMock('Magento\Backend\Model\View\Result\Redirect', [], [], '', false);
        $resultRedirect->expects($this->once())->method('setPath')->with('backup/*');

        $resultRedirectFactory = $this->getMock('Magento\Backend\Model\View\Result\RedirectFactory', [], [], '', false);
        $resultRedirectFactory->expects($this->once())->method('create')->will($this->returnValue($resultRedirect));

        /** @var Download|\PHPUnit_Framework_MockObject_MockObject $controller */
        $controller = (new \Magento\TestFramework\Helper\ObjectManager($this))->getObject(
            'Magento\Backup\Controller\Adminhtml\Index\Download',
            [
                'context' => $context,
                'backupModelFactory' => $backupModelFactory,
                'resultRedirectFactory' => $resultRedirectFactory
            ]
        );
        $this->assertSame($resultRedirect, $controller->execute());
    }

    /**
     * @return array
     */
    public function providerExecuteBackupNotFound()
    {
        return [
            [1, false, $this->once()],
            [0, true, $this->never()],
            [0, false, $this->never()]
        ];
    }
} 
<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Controller\Adminhtml\Export\File;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\ImportExport\Controller\Adminhtml\Export\File\Delete;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var Raw|MockObject
     */
    private $redirectMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var Filesystem|MockObject
     */
    private $fileSystemMock;

    /**
     * @var DriverInterface|MockObject
     */
    private $fileMock;

    /**
     * @var Delete|MockObject
     */
    private $deleteControllerMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var ReadInterface|MockObject
     */
    private $directoryMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileSystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->directoryMock = $this->getMockBuilder(ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->fileMock = $this->getMockBuilder(DriverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->contextMock = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getResultRedirectFactory', 'getMessageManager']
        );

        $this->redirectMock = $this->createPartialMock(Redirect::class, ['setPath']);

        $this->resultRedirectFactoryMock = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );
        $this->resultRedirectFactoryMock->expects($this->any())->method('create')->willReturn($this->redirectMock);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->deleteControllerMock = $this->objectManagerHelper->getObject(
            Delete::class,
            [
                'context' => $this->contextMock,
                'filesystem' => $this->fileSystemMock,
                'file' => $this->fileMock
            ]
        );
    }

    /**
     * Tests download controller with different file names in request.
     */
    public function testExecuteSuccess()
    {
        $this->requestMock->method('getParam')
            ->with('filename')
            ->willReturn('sampleFile');

        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->willReturn($this->directoryMock);
        $this->directoryMock->expects($this->once())->method('isFile')->willReturn(true);
        $this->fileMock->expects($this->once())->method('deleteFile')->willReturn(true);
        $this->messageManagerMock->expects($this->once())->method('addSuccessMessage');

        $this->deleteControllerMock->execute();
    }

    /**
     * Tests download controller with different file names in request.
     */
    public function testExecuteFileDoesntExists()
    {
        $this->requestMock->method('getParam')
            ->with('filename')
            ->willReturn('sampleFile');

        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->willReturn($this->directoryMock);
        $this->directoryMock->expects($this->once())->method('isFile')->willReturn(false);
        $this->messageManagerMock->expects($this->once())->method('addErrorMessage');

        $this->deleteControllerMock->execute();
    }

    /**
     * Test execute() with invalid file name
     * @param string $requestFilename
     * @dataProvider invalidFileDataProvider
     */
    public function testExecuteInvalidFileName($requestFilename)
    {
        $this->requestMock->method('getParam')->with('filename')->willReturn($requestFilename);
        $this->messageManagerMock->expects($this->once())->method('addErrorMessage');

        $this->deleteControllerMock->execute();
    }

    /**
     * Data provider to test possible invalid filenames
     * @return array
     */
    public function invalidFileDataProvider()
    {
        return [
            'Relative file name' => ['../.htaccess'],
            'Empty file name' => [''],
            'Null file name' => [null],
        ];
    }
}

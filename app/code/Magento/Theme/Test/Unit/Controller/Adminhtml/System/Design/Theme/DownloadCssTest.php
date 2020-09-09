<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Theme;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Controller\Adminhtml\System\Design\Theme\DownloadCss;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadCssTest extends TestCase
{
    /**
     * @var Registry|MockObject
     */
    protected $registry;

    /**
     * @var FileFactory|MockObject
     */
    protected $fileFactory;

    /**
     * @var Repository|MockObject
     */
    protected $repository;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystem;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManager;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManager;

    /**
     * @var RedirectInterface|MockObject
     */
    protected $redirect;

    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $response;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactory;

    /**
     * @var DownloadCss
     */
    protected $controller;

    protected function setUp(): void
    {
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $this->redirect = $this->getMockBuilder(RedirectInterface::class)
            ->getMock();
        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->setMethods(['sendResponse', 'setRedirect'])
            ->getMockForAbstractClass();
        $this->objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMock();
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->getMock();
        $this->resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $context->expects($this->any())
            ->method('getRedirect')
            ->willReturn($this->redirect);
        $context->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->response);
        $context->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManager);
        $context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
        $context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);

        $this->registry = $this->getMockBuilder(
            Registry::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->fileFactory = $this->getMockBuilder(FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->repository = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Context $context */
        $this->controller = new DownloadCss(
            $context,
            $this->registry,
            $this->fileFactory,
            $this->repository,
            $this->filesystem
        );
    }

    public function testExecute()
    {
        $themeId = 1;
        $fileParam = '/path/to/file.ext';
        $fileId = 'fileId';
        $sourceFile = '/source/file.ext';
        $relPath = 'file.ext';

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['theme_id', null, $themeId],
                    ['file', null, $fileParam],
                ]
            );
        $file = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $theme = $this->getMockBuilder(ThemeInterface::class)
            ->setMethods(['getId', 'load'])
            ->getMockForAbstractClass();
        $urlDecoder = $this->getMockBuilder(DecoderInterface::class)
            ->getMock();
        $directoryRead = $this->getMockBuilder(ReadInterface::class)
            ->getMock();
        $this->objectManager->expects($this->any())
            ->method('get')
            ->with(DecoderInterface::class)
            ->willReturn($urlDecoder);
        $this->objectManager->expects($this->any())
            ->method('create')
            ->with(ThemeInterface::class)
            ->willReturn($theme);
        $urlDecoder->expects($this->once())
            ->method('decode')
            ->with($fileParam)
            ->willReturn($fileId);
        $theme->expects($this->once())
            ->method('load')
            ->with($themeId)
            ->willReturnSelf();
        $theme->expects($this->once())
            ->method('getId')
            ->willReturn($themeId);
        $this->repository->expects($this->once())
            ->method('createAsset')
            ->with($fileId, ['themeModel' => $theme])
            ->willReturn($file);
        $this->filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->willReturn($directoryRead);
        $file->expects($this->once())
            ->method('getSourceFile')
            ->willReturn($sourceFile);
        $directoryRead->expects($this->once())
            ->method('getRelativePath')
            ->with($sourceFile)
            ->willReturn($relPath);
        $this->fileFactory->expects($this->once())
            ->method('create')
            ->with($relPath, ['type' => 'filename', 'value' => $relPath], DirectoryList::ROOT)
            ->willReturn($this->getMockBuilder(ResponseInterface::class)
            ->getMock());

        $this->assertInstanceOf(ResponseInterface::class, $this->controller->execute());
    }

    public function testExecuteInvalidArgument()
    {
        $themeId = 1;
        $fileParam = '/path/to/file.ext';
        $fileId = 'fileId';
        $refererUrl = 'referer/url';

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['theme_id', null, $themeId],
                    ['file', null, $fileParam],
                ]
            );
        $theme = $this->getMockBuilder(ThemeInterface::class)
            ->setMethods(['getId', 'load'])
            ->getMockForAbstractClass();
        $urlDecoder = $this->getMockBuilder(DecoderInterface::class)
            ->getMock();
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    [DecoderInterface::class, $urlDecoder],
                    [LoggerInterface::class, $logger],
                ]
            );
        $this->objectManager->expects($this->any())
            ->method('create')
            ->with(ThemeInterface::class)
            ->willReturn($theme);
        $urlDecoder->expects($this->once())
            ->method('decode')
            ->with($fileParam)
            ->willReturn($fileId);
        $theme->expects($this->once())
            ->method('load')
            ->with($themeId)
            ->willReturnSelf();
        $theme->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->messageManager->expects($this->once())
            ->method('addException');
        $logger->expects($this->once())
            ->method('critical');
        $this->redirect->expects($this->once())
            ->method('getRefererUrl')
            ->willReturn($refererUrl);
        $this->response->expects($this->once())
            ->method('setRedirect')
            ->with($refererUrl);

        $this->controller->execute();
    }
}

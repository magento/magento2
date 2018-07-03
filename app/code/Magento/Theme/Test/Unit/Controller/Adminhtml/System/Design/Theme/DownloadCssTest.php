<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Theme;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Theme\Controller\Adminhtml\System\Design\Theme\DownloadCss;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadCssTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    /**
     * @var DownloadCss
     */
    protected $controller;

    protected function setUp()
    {
        $context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMock();
        $this->redirect = $this->getMockBuilder(\Magento\Framework\App\Response\RedirectInterface::class)->getMock();
        $this->response = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->setMethods(['sendResponse', 'setRedirect'])
            ->getMock();
        $this->objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)->getMock();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)->getMock();
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
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
            \Magento\Framework\Registry::class
        )->disableOriginalConstructor()->getMock();
        $this->fileFactory = $this->getMockBuilder(\Magento\Framework\App\Response\Http\FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->repository = $this->getMockBuilder(\Magento\Framework\View\Asset\Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Backend\App\Action\Context $context */
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
        $file = $this->getMockBuilder(\Magento\Framework\View\Asset\File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $theme = $this->getMockBuilder(\Magento\Framework\View\Design\ThemeInterface::class)
            ->setMethods(['getId', 'load'])
            ->getMockForAbstractClass();
        $urlDecoder = $this->getMockBuilder(\Magento\Framework\Url\DecoderInterface::class)->getMock();
        $directoryRead = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\ReadInterface::class)->getMock();
        $this->objectManager->expects($this->any())
            ->method('get')
            ->with(\Magento\Framework\Url\DecoderInterface::class)
            ->willReturn($urlDecoder);
        $this->objectManager->expects($this->any())
            ->method('create')
            ->with(\Magento\Framework\View\Design\ThemeInterface::class)
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
            ->willReturn($this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)->getMock());

        $this->assertInstanceOf(\Magento\Framework\App\ResponseInterface::class, $this->controller->execute());
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
        $theme = $this->getMockBuilder(\Magento\Framework\View\Design\ThemeInterface::class)
            ->setMethods(['getId', 'load'])
            ->getMockForAbstractClass();
        $urlDecoder = $this->getMockBuilder(\Magento\Framework\Url\DecoderInterface::class)->getMock();
        $logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)->getMock();
        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    [\Magento\Framework\Url\DecoderInterface::class, $urlDecoder],
                    [\Psr\Log\LoggerInterface::class, $logger],
                ]
            );
        $this->objectManager->expects($this->any())
            ->method('create')
            ->with(\Magento\Framework\View\Design\ThemeInterface::class)
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

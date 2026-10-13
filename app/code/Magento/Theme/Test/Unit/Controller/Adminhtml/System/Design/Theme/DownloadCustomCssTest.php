<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Design\Theme\Customization;
use Magento\Framework\View\Design\Theme\FileInterface;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Theme\Controller\Adminhtml\System\Design\Theme\DownloadCustomCss;
use Magento\Theme\Model\Theme\Customization\File\CustomCss;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadCustomCssTest extends TestCase
{
    use MockCreationTrait;

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
     * @var DownloadCustomCss
     */
    protected $controller;

    protected function setUp(): void
    {
        $context = $this->createMock(Context::class);
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $this->redirect = $this->createMock(RedirectInterface::class);
        $this->response = $this->createPartialMockWithReflection(
            ResponseInterface::class,
            ['sendResponse', 'setRedirect']
        );
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->resultFactory = $this->createMock(ResultFactory::class);
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

        $this->registry = $this->createMock(
            Registry::class
        );
        $this->fileFactory = $this->createMock(FileFactory::class);
        $this->repository = $this->createMock(Repository::class);
        $this->filesystem = $this->createMock(Filesystem::class);

        /** @var Context $context */
        $this->controller = new DownloadCustomCss(
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
        $fileName = 'file.ext';
        $fullPath = 'path/to/file';

        $file = $this->getMockBuilder(FileInterface::class)
            ->getMock();
        $customization = $this->createMock(Customization::class);
        $theme = $this->createPartialMockWithReflection(
            ThemeInterface::class,
            [
                'getArea', 'getThemePath', 'getFullPath', 'getParentTheme',
                'getCode', 'isPhysical', 'getInheritedThemes', 'getId',
                'getCustomization'
            ]
        );
        $file->expects($this->once())
            ->method('getContent')
            ->willReturn('some_content');
        $file->expects($this->once())
            ->method('getFilename')
            ->willReturn($fileName);
        $file->expects($this->once())
            ->method('getFullPath')
            ->willReturn($fullPath);
        $theme->method('getId')
            ->willReturn($themeId);
        $theme->expects($this->once())
            ->method('getCustomization')
            ->willReturn($customization);
        $customization->expects($this->once())
            ->method('getFilesByType')
            ->with(CustomCss::TYPE)
            ->willReturn([$file]);
        $this->request->expects($this->any())
            ->method('getParam')
            ->with('theme_id')
            ->willReturn($themeId);
        $themeFactory = $this->getMockBuilder(FlyweightFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager->expects($this->any())
            ->method('create')
            ->with(FlyweightFactory::class)
            ->willReturn($themeFactory);
        $themeFactory->expects($this->once())
            ->method('create')
            ->with($themeId)
            ->willReturn($theme);
        $this->fileFactory->expects($this->once())
            ->method('create')
            ->with($fileName, ['type' => 'filename', 'value' => $fullPath], DirectoryList::ROOT)
            ->willReturn($this->getMockBuilder(ResponseInterface::class)
            ->getMock());

        $this->assertInstanceOf(ResponseInterface::class, $this->controller->execute());
    }

    public function testExecuteInvalidArgument()
    {
        $themeId = 1;
        $refererUrl = 'referer/url';

        $this->request->expects($this->any())
            ->method('getParam')
            ->with('theme_id')
            ->willReturn($themeId);
        $themeFactory = $this->getMockBuilder(FlyweightFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $this->objectManager->expects($this->any())
            ->method('get')
            ->with(LoggerInterface::class)
            ->willReturn($logger);
        $this->objectManager->expects($this->any())
            ->method('create')
            ->with(FlyweightFactory::class)
            ->willReturn($themeFactory);
        $themeFactory->expects($this->once())
            ->method('create')
            ->with($themeId)
            ->willReturn(null);
        $this->messageManager->expects($this->once())
            ->method('addExceptionMessage');
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

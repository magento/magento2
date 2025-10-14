<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Theme;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Controller\Adminhtml\System\Design\Theme\Delete;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends TestCase
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
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactory;

    /**
     * @var Delete
     */
    protected $controller;

    protected function setUp(): void
    {
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
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
        $this->controller = new Delete(
            $context,
            $this->registry,
            $this->fileFactory,
            $this->repository,
            $this->filesystem
        );
    }

    public function testExecute()
    {
        $path = 'adminhtml/*/';
        $themeId = 1;
        $theme = $this->getMockBuilder(ThemeInterface::class)
            ->addMethods(['load', 'isVirtual', 'delete'])
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, $themeId],
                    ['back', false, true],
                ]
            );
        $redirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(ThemeInterface::class)
            ->willReturn($theme);
        $theme->expects($this->once())
            ->method('load')
            ->with($themeId)
            ->willReturnSelf();
        $theme->expects($this->once())
            ->method('delete')
            ->willReturnSelf();
        $theme->expects($this->once())
            ->method('getId')
            ->willReturn($themeId);
        $theme->expects($this->once())
            ->method('isVirtual')
            ->willReturn(true);
        $this->messageManager->expects($this->once())
            ->method('addSuccess')
            ->willReturnSelf();
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($redirect);
        $redirect->expects($this->once())
            ->method('setPath')
            ->with($path)
            ->willReturnSelf();

        $this->assertInstanceOf(Redirect::class, $this->controller->execute());
    }

    /**
     * @return array
     */
    public static function invalidArgumentDataProvider()
    {
        return [
            'themeId'   => [null, true],
            'isVirtual' => [1, false],
        ];
    }

    /**
     * @param int|null $themeIdInModel
     * @param bool $isVirtual
     * @test
     * @return void
     * @dataProvider invalidArgumentDataProvider
     */
    public function testExecuteInvalidArgument($themeIdInModel, $isVirtual)
    {
        $path = 'adminhtml/*/';
        $themeId = 1;
        $theme = $this->getMockBuilder(ThemeInterface::class)
            ->addMethods(['load', 'isVirtual'])
            ->onlyMethods([ 'getId'])
            ->getMockForAbstractClass();
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, $themeId],
                    ['back', false, false],
                ]
            );
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $redirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(ThemeInterface::class)
            ->willReturn($theme);
        $theme->expects($this->once())
            ->method('load')
            ->with($themeId)
            ->willReturnSelf();
        $theme->expects($this->once())
            ->method('getId')
            ->willReturn($themeIdInModel);
        $theme->expects($this->any())
            ->method('isVirtual')
            ->willReturn($isVirtual);
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($redirect);
        $redirect->expects($this->once())
            ->method('setPath')
            ->with($path)
            ->willReturnSelf();
        $this->messageManager->expects($this->once())
            ->method('addException');
        $this->objectManager->expects($this->once())
            ->method('get')
            ->with(LoggerInterface::class)
            ->willReturn($logger);
        $logger->expects($this->once())
            ->method('critical');

        $this->controller->execute();
    }

    /**
     * @test
     * @return void
     */
    public function testExecuteLocalizedException()
    {
        $path = 'adminhtml/*/';
        $themeId = 1;
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, $themeId],
                    ['back', false, false],
                ]
            );
        $redirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(ThemeInterface::class)
            ->willThrowException(new LocalizedException(__('localized exception')));
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($redirect);
        $redirect->expects($this->once())
            ->method('setPath')
            ->with($path)
            ->willReturnSelf();
        $this->messageManager->expects($this->once())
            ->method('addError');

        $this->assertInstanceOf(Redirect::class, $this->controller->execute());
    }
}

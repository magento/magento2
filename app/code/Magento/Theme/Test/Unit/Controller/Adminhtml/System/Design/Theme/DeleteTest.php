<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Theme;

use Magento\Framework\Controller\ResultFactory;
use Magento\Theme\Controller\Adminhtml\System\Design\Theme\Delete;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $repository;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultFactory;

    /**
     * @var Delete
     */
    protected $controller;

    protected function setUp(): void
    {
        $context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMock();
        $this->objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)->getMock();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)->getMock();
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
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
        $theme = $this->getMockBuilder(\Magento\Framework\View\Design\ThemeInterface::class)
            ->setMethods(['load', 'getId', 'isVirtual', 'delete'])
            ->getMockForAbstractClass();
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, $themeId],
                    ['back', false, true],
                ]
            );
        $redirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\View\Design\ThemeInterface::class)
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

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $this->controller->execute());
    }

    /**
     * @return array
     */
    public function invalidArgumentDataProvider()
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
        $theme = $this->getMockBuilder(\Magento\Framework\View\Design\ThemeInterface::class)
            ->setMethods(['load', 'getId', 'isVirtual'])
            ->getMockForAbstractClass();
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, $themeId],
                    ['back', false, false],
                ]
            );
        $logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)->getMock();
        $redirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\View\Design\ThemeInterface::class)
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
            ->with(\Psr\Log\LoggerInterface::class)
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
        $redirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\View\Design\ThemeInterface::class)
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('localized exception')));
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

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $this->controller->execute());
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Validator\StringLength;
use Magento\MediaStorage\Model\File\Validator\AvailablePath;
use Magento\Sitemap\Controller\Adminhtml\Sitemap\Save;
use Magento\Sitemap\Helper\Data;
use Magento\Sitemap\Model\SitemapFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends TestCase
{
    /**
     * @var Save
     */
    private $saveController;

    /**
     * @var Context
     */
    private $contextMock;

    /**
     * @var Request|MockObject
     */
    private $requestMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirectMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var StringLength|MockObject
     */
    private $lengthValidator;

    /**
     * @var AvailablePath|MockObject
     */
    private $pathValidator;

    /**
     * @var Data|MockObject
     */
    private $helper;

    /**
     * @var Filesystem|MockObject
     */
    private $fileSystem;

    /**
     * @var SitemapFactory|MockObject
     */
    private $siteMapFactory;

    /**
     * @var Session|MockObject
     */
    private $session;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPostValue'])
            ->getMockForAbstractClass();
        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMock();
        $this->helper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirectMock);
        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['setFormData'])
            ->getMock();

        $this->contextMock->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $this->contextMock->expects($this->once())
            ->method('getSession')
            ->willReturn($this->session);

        $this->lengthValidator = $this->getMockBuilder(StringLength::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pathValidator =
            $this->getMockBuilder(AvailablePath::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->fileSystem = $this->createMock(Filesystem::class);
        $this->siteMapFactory = $this->createMock(SitemapFactory::class);

        $this->saveController = new Save(
            $this->contextMock,
            $this->lengthValidator,
            $this->pathValidator,
            $this->helper,
            $this->fileSystem,
            $this->siteMapFactory
        );
    }

    /**
     * @return void
     */
    public function testSaveEmptyDataShouldRedirectToDefault(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn([]);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->saveController->execute());
    }

    /**
     * @return void
     */
    public function testTryToSaveInvalidDataShouldFailWithErrors(): void
    {
        $validPaths = [];
        $messages = ['message1', 'message2'];
        $data = ['sitemap_filename' => 'sitemap_filename', 'sitemap_path' => '/sitemap_path'];
        $siteMapId = 1;

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('sitemap_id')
            ->willReturn($siteMapId);

        $this->pathValidator->expects($this->once())
            ->method('setPaths')
            ->with($validPaths)
            ->willReturnSelf();
        $this->pathValidator->expects($this->once())
            ->method('isValid')
            ->with('/sitemap_path/sitemap_filename')
            ->willReturn(false);
        $this->pathValidator->expects($this->once())
            ->method('getMessages')
            ->willReturn($messages);

        $this->helper->expects($this->once())
            ->method('getValidPaths')
            ->willReturn($validPaths);

        $this->session->expects($this->once())
            ->method('setFormData')
            ->with($data)
            ->willReturnSelf();

        $this->messageManagerMock
            ->method('addErrorMessage')
            ->willReturn($this->messageManagerMock);

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/*/edit', ['sitemap_id' => $siteMapId])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->saveController->execute());
    }

    /**
     * @return void
     */
    public function testTryToSaveInvalidFileNameShouldFailWithErrors(): void
    {
        $validPaths = [];
        $messages = ['message1', 'message2'];
        $data = ['sitemap_filename' => 'sitemap_filename', 'sitemap_path' => '/sitemap_path'];
        $siteMapId = 1;

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('sitemap_id')
            ->willReturn($siteMapId);

        $this->lengthValidator->expects($this->once())
            ->method('isValid')
            ->with('sitemap_filename')
            ->willReturn(false);
        $this->lengthValidator->expects($this->once())
            ->method('getMessages')
            ->willReturn($messages);

        $this->pathValidator->expects($this->once())
            ->method('setPaths')
            ->with($validPaths)
            ->willReturnSelf();
        $this->pathValidator->expects($this->once())
            ->method('isValid')
            ->with('/sitemap_path/sitemap_filename')
            ->willReturn(true);

        $this->helper->expects($this->once())
            ->method('getValidPaths')
            ->willReturn($validPaths);

        $this->session->expects($this->once())
            ->method('setFormData')
            ->with($data)
            ->willReturnSelf();

        $this->messageManagerMock
            ->method('addErrorMessage')
            ->willReturn($this->messageManagerMock);

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/*/edit', ['sitemap_id' => $siteMapId])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->saveController->execute());
    }
}

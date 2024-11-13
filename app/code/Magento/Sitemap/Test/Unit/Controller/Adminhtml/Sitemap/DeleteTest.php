<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sitemap\Controller\Adminhtml\Sitemap\Delete;
use Magento\Sitemap\Model\SitemapFactory;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends TestCase
{
    /**
     * @var Context
     */
    private $contextMock;

    /**
     * @var Request
     */
    private $requestMock;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManagerMock;

    /**
     * @var ManagerInterface
     */
    private $messageManagerMock;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var SitemapFactory
     */
    private $siteMapFactory;

    /**
     * @var Delete
     */
    private $deleteController;

    /**
     * @var Session
     */
    private $sessionMock;

    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var Data
     */
    private $helperMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getParam'])
            ->getMockForAbstractClass();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['setIsUrlNotice'])
            ->getMock();
        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->addMethods(['setRedirect'])
            ->onlyMethods(['sendResponse'])
            ->getMockForAbstractClass();
        $this->response->expects($this->once())->method('setRedirect');
        $this->sessionMock->expects($this->any())->method('setIsUrlNotice')->willReturn($this->objectManager);
        $this->actionFlag = $this->createPartialMock(ActionFlag::class, ['get']);
        $this->actionFlag->expects($this->any())->method("get")->willReturn($this->objectManager);
        $this->objectManager = $this->getMockBuilder(ObjectManager::class)
            ->addMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMock();
        $this->helperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUrl'])
            ->getMock();
        $this->helperMock->expects($this->any())
            ->method('getUrl')
            ->willReturn('adminhtml/*/');
        $this->contextMock->expects($this->any())
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $this->contextMock->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);
        $this->contextMock->expects($this->any())
            ->method('getHelper')
            ->willReturn($this->helperMock);
        $this->contextMock->expects($this->any())->method("getActionFlag")->willReturn($this->actionFlag);
        $this->fileSystem = $this->createMock(Filesystem::class);
        $this->siteMapFactory = $this->createMock(SitemapFactory::class);
        $this->deleteController = new Delete(
            $this->contextMock,
            $this->siteMapFactory,
            $this->fileSystem
        );
    }

    public function testDelete()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn(null);

        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');
        $this->deleteController->execute();
    }
}

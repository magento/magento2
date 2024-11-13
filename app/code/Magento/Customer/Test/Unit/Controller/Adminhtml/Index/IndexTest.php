<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Customer\Controller\Adminhtml\Index\Index;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Customer\Controller\Adminhtml\Index\Index
 */
class IndexTest extends TestCase
{
    /**
     * @var Index
     */
    protected $indexController;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Request|MockObject
     */
    protected $requestMock;

    /**
     * @var ForwardFactory|MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var Forward|MockObject
     */
    protected $resultForwardMock;

    /**
     * @var PageFactory|MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var Page|MockObject
     */
    protected $resultPageMock;

    /**
     * @var Config|MockObject
     */
    protected $pageConfigMock;

    /**
     * @var Title|MockObject
     */
    protected $pageTitleMock;

    /**
     * @var Session|MockObject
     */
    protected $sessionMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardFactoryMock = $this->getMockBuilder(
            ForwardFactory::class
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->addMethods(['setActiveMenu', 'addBreadcrumb'])
            ->onlyMethods(['getConfig'])
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['unsCustomerData', 'unsCustomerFormData'])
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'session' => $this->sessionMock
            ]
        );
        $this->indexController = $objectManager->getObject(
            Index::class,
            [
                'context' => $this->context,
                'resultForwardFactory' => $this->resultForwardFactoryMock,
                'resultPageFactory' => $this->resultPageFactoryMock
            ]
        );
    }

    /**
     * @covers \Magento\Customer\Controller\Adminhtml\Index\Index::execute
     */
    public function testExecute()
    {
        $this->prepareExecute();

        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->with('Magento_Customer::customer_manage');
        $this->resultPageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);
        $this->pageTitleMock->expects($this->once())
            ->method('prepend')
            ->with('Customers');
        $this->resultPageMock->expects($this->atLeastOnce())
            ->method('addBreadcrumb')
            ->willReturnCallback(
                function ($arg1, $arg2) {
                    if ($arg1 == 'Customers' && $arg2 == 'Customers') {
                        return null;
                    } elseif ($arg1 == 'Manage Customers' && $arg2 == 'Manage Customers') {
                        return null;
                    }
                }
            );
        $this->sessionMock->expects($this->once())
            ->method('unsCustomerData');
        $this->sessionMock->expects($this->once())
            ->method('unsCustomerFormData');

        $this->assertInstanceOf(
            Page::class,
            $this->indexController->execute()
        );
    }

    /**
     * @covers \Magento\Customer\Controller\Adminhtml\Index\Index::execute
     */
    public function testExecuteAjax()
    {
        $this->prepareExecute(true);

        $this->resultForwardFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultForwardMock);
        $this->resultForwardMock->expects($this->once())
            ->method('forward')
            ->with('grid')
            ->willReturnSelf();

        $this->assertInstanceOf(
            Forward::class,
            $this->indexController->execute()
        );
    }

    /**
     * @param bool $ajax
     */
    protected function prepareExecute($ajax = false)
    {
        $this->requestMock->expects($this->once())
            ->method('getQuery')
            ->with('ajax')
            ->willReturn($ajax);
    }
}

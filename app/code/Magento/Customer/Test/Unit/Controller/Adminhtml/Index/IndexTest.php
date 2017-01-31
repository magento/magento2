<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Index;

/**
 * @covers \Magento\Customer\Controller\Adminhtml\Index\Index
 */
class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Controller\Adminhtml\Index\Index
     */
    protected $indexController;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultForwardMock;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageTitleMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()->getMock();
        $this->resultForwardFactoryMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\ForwardFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\Forward')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder('Magento\Framework\View\Result\PageFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->setMethods(['setActiveMenu', 'getConfig', 'addBreadcrumb'])
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder('Magento\Framework\View\Page\Title')
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder('Magento\Backend\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods(['unsCustomerData', 'unsCustomerFormData'])
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $objectManager->getObject(
            'Magento\Backend\App\Action\Context',
            [
                'request' => $this->requestMock,
                'session' => $this->sessionMock
            ]
        );
        $this->indexController = $objectManager->getObject(
            'Magento\Customer\Controller\Adminhtml\Index\Index',
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
            ->withConsecutive(
                ['Customers', 'Customers'],
                ['Manage Customers', 'Manage Customers']
            );
        $this->sessionMock->expects($this->once())
            ->method('unsCustomerData');
        $this->sessionMock->expects($this->once())
            ->method('unsCustomerFormData');

        $this->assertInstanceOf(
            'Magento\Framework\View\Result\Page',
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
            'Magento\Backend\Model\View\Result\Forward',
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

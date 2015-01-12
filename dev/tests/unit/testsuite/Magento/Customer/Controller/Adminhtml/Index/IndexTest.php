<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

/**
 * Class IndexTest
 */
class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFlagMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $titleMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutInterfaceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $breadcrumbsBlockMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $menuBlockMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewInterfaceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Customer\Controller\Adminhtml\Index\Index
     */
    protected $controller;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->setMethods(['setRedirect', 'getHeader', '__wakeup'])
            ->getMock();
        $this->sessionMock = $this->getMockBuilder('Magento\Backend\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods(['unsCustomerData', '__wakeup', 'setIsUrlNotice'])
            ->getMock();
        $this->actionFlagMock = $this->getMockBuilder('Magento\Framework\App\ActionFlag')
            ->disableOriginalConstructor()
            ->getMock();
        $this->titleMock = $this->getMockBuilder('Magento\Framework\View\Page\Title')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->breadcrumbsBlockMock = $this->getMockBuilder('Magento\Backend\Block\Widget\Breadcrumbs')
            ->disableOriginalConstructor()
            ->getMock();

        $this->menuBlockMock = $this->getMockBuilder('Magento\Backend\Block\Menu')
            ->disableOriginalConstructor()
            ->setMethods(['getMenuModel', 'getParentItems'])
            ->getMock();
        $this->menuBlockMock->expects($this->any())
            ->method('getMenuModel')
            ->willReturnSelf();
        $this->menuBlockMock->expects($this->any())
            ->method('getParentItems')
            ->willReturn([]);

        $this->layoutInterfaceMock = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewInterfaceMock = $this->getMockBuilder('Magento\Framework\App\ViewInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewInterfaceMock->expects($this->any())->method('getPage')->will(
            $this->returnValue($this->resultPageMock)
        );
        $this->resultPageMock->expects($this->any())->method('getConfig')->will(
            $this->returnValue($this->pageConfigMock)
        );

        $this->pageConfigMock->expects($this->any())->method('getTitle')->will($this->returnValue($this->titleMock));

        $this->contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $this->contextMock->expects($this->any())
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $this->contextMock->expects($this->any())
            ->method('getActionFlag')
            ->willReturn($this->actionFlagMock);
        $this->contextMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($this->titleMock);
        $this->contextMock->expects($this->any())
            ->method('getView')
            ->willReturn($this->viewInterfaceMock);

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            'Magento\Customer\Controller\Adminhtml\Index\Index',
            [
                'context' => $this->contextMock,
            ]
        );
    }

    public function testExecuteAjax()
    {
        $this->requestMock->expects($this->once())
            ->method('getQuery')
            ->with('ajax')
            ->willReturn(true);
        $this->assertNull($this->controller->execute());
    }

    public function testExecute()
    {
        $this->titleMock->expects($this->once())->method('prepend')->with(__('Customers'));
        $this->viewInterfaceMock->expects($this->any())->method('getLayout')->will(
            $this->returnValue($this->layoutInterfaceMock)
        );
        $this->layoutInterfaceMock->expects($this->at(0))
            ->method('getBlock')
            ->with('menu')
            ->willReturn($this->menuBlockMock);

        $this->layoutInterfaceMock->expects($this->at(1))
            ->method('getBlock')
            ->with('breadcrumbs')
            ->willReturn($this->breadcrumbsBlockMock);
        $this->layoutInterfaceMock->expects($this->at(2))
            ->method('getBlock')
            ->with('breadcrumbs')
            ->willReturn($this->breadcrumbsBlockMock);

        $this->requestMock->expects($this->once())
            ->method('getQuery')
            ->with('ajax')
            ->willReturn(false);
        $this->sessionMock->expects($this->once())
            ->method('unsCustomerData');
        $this->assertNull($this->controller->execute());
    }
}

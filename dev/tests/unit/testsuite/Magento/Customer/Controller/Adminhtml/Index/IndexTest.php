<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected $viewMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $breadcrumbsBlockMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $menuBlockMock;

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
        $this->titleMock = $this->getMockBuilder('Magento\Framework\App\Action\Title')
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

        $this->layoutMock = $this->getMockBuilder('Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->setMethods(['getBlock'])
            ->getMock();

        $this->viewMock = $this->getMockBuilder('Magento\Backend\Model\View')
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layoutMock);

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
            ->willReturn($this->viewMock);

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
        $this->layoutMock->expects($this->at(0))
            ->method('getBlock')
            ->with('menu')
            ->willReturn($this->menuBlockMock);

        $this->layoutMock->expects($this->at(1))
            ->method('getBlock')
            ->with('breadcrumbs')
            ->willReturn($this->breadcrumbsBlockMock);
        $this->layoutMock->expects($this->at(2))
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

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml;

abstract class ProductTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $context;
    /** @var \Magento\Catalog\Controller\Product */
    protected $action;
    /** @var \Magento\Framework\View\Layout  */
    protected $layout;
    /** @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $session;
    /** @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /**
     *  Init context object
     */
    protected function initContext()
    {
        $productActionMock = $this->getMock('Magento\Catalog\Model\Product\Action', [], [], '', false);
        $objectManagerMock = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $objectManagerMock->expects($this->any())->method('get')->will($this->returnValue($productActionMock));

        $block = $this->getMockBuilder('\Magento\Framework\View\Element\AbstractBlock')
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->layout = $this->getMockBuilder('Magento\Framework\View\Layout')
            ->setMethods(['getBlock'])->disableOriginalConstructor()
            ->getMock();
        $this->layout->expects($this->any())->method('getBlock')->will($this->returnValue($block));

        $eventManager = $this->getMockBuilder('Magento\Framework\Event\Manager')
            ->setMethods(['dispatch'])->disableOriginalConstructor()->getMock();
        $eventManager->expects($this->any())->method('dispatch')->will($this->returnSelf());
        $title = $this->getMockBuilder('\Magento\Framework\App\Action\Title')
            ->setMethods(['add'])->disableOriginalConstructor()->getMock();
        $title->expects($this->any())->method('prepend')->withAnyParameters()->will($this->returnSelf());
        $requestInterfaceMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')->setMethods(
            ['getParam', 'getFullActionName', 'getPost']
        )->disableOriginalConstructor()->getMock();

        $responseInterfaceMock = $this->getMockBuilder('Magento\Framework\App\ResponseInterface')->setMethods(
            ['setRedirect', 'sendResponse']
        )->getMock();

        $managerInterfaceMock = $this->getMock('Magento\Framework\Message\ManagerInterface');
        $sessionMock = $this->getMock(
            'Magento\Backend\Model\Session',
            ['getProductData', 'setProductData'],
            [],
            '',
            false
        );
        $actionFlagMock = $this->getMock('Magento\Framework\App\ActionFlag', [], [], '', false);
        $helperDataMock = $this->getMock('Magento\Backend\Helper\Data', [], [], '', false);
        $this->context = $this->getMock(
            'Magento\Backend\App\Action\Context',
            [
                'getRequest',
                'getResponse',
                'getObjectManager',
                'getEventManager',
                'getMessageManager',
                'getSession',
                'getActionFlag',
                'getHelper',
                'getTitle',
                'getView'
            ],
            [],
            '',
            false
        );

        $this->context->expects($this->any())->method('getTitle')->will($this->returnValue($title));
        $this->context->expects($this->any())->method('getEventManager')->will($this->returnValue($eventManager));
        $this->context->expects($this->any())->method('getRequest')->will($this->returnValue($requestInterfaceMock));
        $this->context->expects($this->any())->method('getResponse')->will($this->returnValue($responseInterfaceMock));
        $this->context->expects($this->any())->method('getObjectManager')->will($this->returnValue($objectManagerMock));

        $this->context->expects($this->any())->method('getMessageManager')
            ->will($this->returnValue($managerInterfaceMock));
        $this->context->expects($this->any())->method('getSession')->will($this->returnValue($sessionMock));
        $this->context->expects($this->any())->method('getActionFlag')->will($this->returnValue($actionFlagMock));
        $this->context->expects($this->any())->method('getHelper')->will($this->returnValue($helperDataMock));

        $this->session = $sessionMock;
        $this->request = $requestInterfaceMock;

        return $this->context;
    }
}

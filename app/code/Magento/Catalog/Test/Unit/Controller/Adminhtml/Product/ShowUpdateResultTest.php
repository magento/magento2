<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use Magento\Catalog\Controller\Adminhtml\Product\ShowUpdateResult;

class ShowUpdateResultTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject */
    protected $layout;

    /** @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $session;

    /** @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /**
     * Init session object
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSession()
    {
        $session = $this->getMock(
            'Magento\Backend\Model\Session',
            ['hasCompositeProductResult', 'getCompositeProductResult', 'unsCompositeProductResult'],
            [],
            '',
            false
        );
        $session->expects($this->once())
            ->method('hasCompositeProductResult')
            ->willReturn(true);
        $session->expects($this->once())
            ->method('unsCompositeProductResult');
        $session->expects($this->atLeastOnce())
            ->method('getCompositeProductResult')
            ->willReturn(new \Magento\Framework\DataObject());

        return $session;
    }

    /**
     * Init context object
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContext()
    {
        $productActionMock = $this->getMock('Magento\Catalog\Model\Product\Action', [], [], '', false);
        $objectManagerMock = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->willreturn($productActionMock);

        $eventManager = $this->getMockForAbstractClass('Magento\Framework\Event\Manager', ['dispatch'], '', false);

        $eventManager->expects($this->any())
            ->method('dispatch')
            ->willReturnSelf();

        $this->request = $this->getMock(
            'Magento\Framework\App\Request\Http',
            ['getParam', 'getPost', 'getFullActionName', 'getPostValue'],
            [],
            '',
            false
        );

        $responseInterfaceMock = $this->getMock(
            'Magento\Framework\App\ResponseInterface',
            ['setRedirect', 'sendResponse'],
            [],
            '',
            false
        );

        $managerInterfaceMock = $this->getMock('Magento\Framework\Message\ManagerInterface');
        $this->session = $this->getSession();
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
                'getView',
                'getResultRedirectFactory'
            ],
            [],
            '',
            false
        );
        
        $this->context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManager);
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context->expects($this->any())
            ->method('getResponse')
            ->willReturn($responseInterfaceMock);
        $this->context->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);

        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($managerInterfaceMock);
        $this->context->expects($this->any())
            ->method('getSession')
            ->willReturn($this->session);
        $this->context->expects($this->any())
            ->method('getActionFlag')
            ->willReturn($actionFlagMock);
        $this->context->expects($this->any())
            ->method('getHelper')
            ->willReturn($helperDataMock);

        return $this->context;
    }

    public function testExecute()
    {
        $productCompositeHelper = $this->getMock('Magento\Catalog\Helper\Product\Composite', [], [], '', false);
        $productCompositeHelper->expects($this->once())
            ->method('renderUpdateResult');

        $productBuilder = $this->getMock('Magento\Catalog\Controller\Adminhtml\Product\Builder', [], [], '', false);
        $context = $this->getContext();

        /** @var \Magento\Catalog\Controller\Adminhtml\Product\ShowUpdateResult $controller */
        $controller = new ShowUpdateResult($context, $productBuilder, $productCompositeHelper);
        $controller->execute();
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use Magento\Catalog\Controller\Adminhtml\Product\ShowUpdateResult;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
            \Magento\Backend\Model\Session::class,
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
        $productActionMock = $this->getMock(\Magento\Catalog\Model\Product\Action::class, [], [], '', false);
        $objectManagerMock = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->willreturn($productActionMock);

        $eventManager = $this->getMockForAbstractClass(
            \Magento\Framework\Event\Manager::class,
            ['dispatch'],
            '',
            false
        );

        $eventManager->expects($this->any())
            ->method('dispatch')
            ->willReturnSelf();

        $this->request = $this->getMock(
            \Magento\Framework\App\Request\Http::class,
            ['getParam', 'getPost', 'getFullActionName', 'getPostValue'],
            [],
            '',
            false
        );

        $responseInterfaceMock = $this->getMock(
            \Magento\Framework\App\ResponseInterface::class,
            ['setRedirect', 'sendResponse'],
            [],
            '',
            false
        );

        $managerInterfaceMock = $this->getMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->session = $this->getSession();
        $actionFlagMock = $this->getMock(\Magento\Framework\App\ActionFlag::class, [], [], '', false);
        $helperDataMock = $this->getMock(\Magento\Backend\Helper\Data::class, [], [], '', false);
        $this->context = $this->getMock(
            \Magento\Backend\App\Action\Context::class,
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
        $productCompositeHelper = $this->getMock(\Magento\Catalog\Helper\Product\Composite::class, [], [], '', false);
        $productCompositeHelper->expects($this->once())
            ->method('renderUpdateResult');

        $productBuilder = $this->getMock(
            \Magento\Catalog\Controller\Adminhtml\Product\Builder::class,
            [],
            [],
            '',
            false
        );
        $context = $this->getContext();

        /** @var \Magento\Catalog\Controller\Adminhtml\Product\ShowUpdateResult $controller */
        $controller = new ShowUpdateResult($context, $productBuilder, $productCompositeHelper);
        $controller->execute();
    }
}

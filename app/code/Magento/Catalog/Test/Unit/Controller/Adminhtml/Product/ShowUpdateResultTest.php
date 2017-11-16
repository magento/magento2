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
class ShowUpdateResultTest extends \PHPUnit\Framework\TestCase
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
        $session = $this->createPartialMock(
            \Magento\Backend\Model\Session::class,
            ['hasCompositeProductResult', 'getCompositeProductResult', 'unsCompositeProductResult']
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
        $productActionMock = $this->createMock(\Magento\Catalog\Model\Product\Action::class);
        $objectManagerMock = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->willreturn($productActionMock);

        $eventManager = $this->getMockBuilder(\Magento\Framework\Event\Manager::class)
            ->setMethods(['dispatch'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $eventManager->expects($this->any())
            ->method('dispatch')
            ->willReturnSelf();

        $this->request = $this->createPartialMock(
            \Magento\Framework\App\Request\Http::class,
            ['getParam', 'getPost', 'getFullActionName', 'getPostValue']
        );

        $responseInterfaceMock = $this->createPartialMock(
            \Magento\Framework\App\ResponseInterface::class,
            ['setRedirect', 'sendResponse']
        );

        $managerInterfaceMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->session = $this->getSession();
        $actionFlagMock = $this->createMock(\Magento\Framework\App\ActionFlag::class);
        $helperDataMock = $this->createMock(\Magento\Backend\Helper\Data::class);
        $this->context = $this->createPartialMock(\Magento\Backend\App\Action\Context::class, [
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
            ]);

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
        $productCompositeHelper = $this->createMock(\Magento\Catalog\Helper\Product\Composite::class);
        $productCompositeHelper->expects($this->once())
            ->method('renderUpdateResult');

        $productBuilder = $this->createMock(\Magento\Catalog\Controller\Adminhtml\Product\Builder::class);
        $context = $this->getContext();

        /** @var \Magento\Catalog\Controller\Adminhtml\Product\ShowUpdateResult $controller */
        $controller = new ShowUpdateResult($context, $productBuilder, $productCompositeHelper);
        $controller->execute();
    }
}

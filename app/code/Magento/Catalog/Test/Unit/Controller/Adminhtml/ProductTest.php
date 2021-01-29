<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class ProductTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $context;

    /** @var \Magento\Catalog\Controller\Product */
    protected $action;

    /** @var \Magento\Framework\View\Layout  */
    protected $layout;

    /** @var \Magento\Backend\Model\Session|\PHPUnit\Framework\MockObject\MockObject */
    protected $session;

    /** @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject */
    protected $request;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManagerMock;

    /**
     * Init context object
     *
     * @param array $additionalParams
     * @param array $objectManagerMap Object Manager mappings
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function initContext(array $additionalParams = [], array $objectManagerMap = [])
    {
        $productActionMock = $this->createMock(\Magento\Catalog\Model\Product\Action::class);

        $this->objectManagerMock = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);

        if ($objectManagerMap) {
            $this->objectManagerMock->expects($this->any())
                ->method('get')
                ->willReturnMap($objectManagerMap);
        }

        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturn($productActionMock);

        $block = $this->getMockBuilder(\Magento\Framework\View\Element\AbstractBlock::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->layout = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->setMethods(['getBlock'])->disableOriginalConstructor()
            ->getMock();
        $this->layout->expects($this->any())->method('getBlock')->willReturn($block);

        $eventManager = $this->getMockBuilder(\Magento\Framework\Event\Manager::class)
            ->setMethods(['dispatch'])->disableOriginalConstructor()->getMock();
        $eventManager->expects($this->any())->method('dispatch')->willReturnSelf();
        $title = $this->getMockBuilder(\Magento\Framework\App\Action\Title::class)
            ->setMethods(['add', 'prepend'])->disableOriginalConstructor()->getMock();
        $title->expects($this->any())->method('prepend')->withAnyParameters()->willReturnSelf();
        $requestInterfaceMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)->setMethods(
            ['getParam', 'getPost', 'getFullActionName', 'getPostValue']
        )->disableOriginalConstructor()->getMock();

        $responseInterfaceMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)->setMethods(
            ['setRedirect', 'sendResponse']
        )->getMock();

        $managerInterfaceMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $sessionMock = $this->createPartialMock(
            \Magento\Backend\Model\Session::class,
            ['getProductData', 'setProductData']
        );
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
                'getResultRedirectFactory',
                'getResultFactory'
            ]);

        $this->context->expects($this->any())->method('getTitle')->willReturn($title);
        $this->context->expects($this->any())->method('getEventManager')->willReturn($eventManager);
        $this->context->expects($this->any())->method('getRequest')->willReturn($requestInterfaceMock);
        $this->context->expects($this->any())->method('getResponse')->willReturn($responseInterfaceMock);
        $this->context->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);

        $this->context->expects($this->any())->method('getMessageManager')
            ->willReturn($managerInterfaceMock);
        $this->context->expects($this->any())->method('getSession')->willReturn($sessionMock);
        $this->context->expects($this->any())->method('getActionFlag')->willReturn($actionFlagMock);
        $this->context->expects($this->any())->method('getHelper')->willReturn($helperDataMock);

        foreach ($additionalParams as $property => $object) {
            $this->context->expects($this->any())->method('get' . ucfirst($property))->willReturn($object);
        }

        $this->session = $sessionMock;
        $this->request = $requestInterfaceMock;

        return $this->context;
    }
}

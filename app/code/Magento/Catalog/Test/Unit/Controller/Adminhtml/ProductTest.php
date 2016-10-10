<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * Init context object
     *
     * @param array $additionalParams
     * @param array $objectManagerMap Object Manager mappings
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function initContext(array $additionalParams = [], array $objectManagerMap = [])
    {
        $productActionMock = $this->getMock(\Magento\Catalog\Model\Product\Action::class, [], [], '', false);

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
        $this->layout->expects($this->any())->method('getBlock')->will($this->returnValue($block));

        $eventManager = $this->getMockBuilder(\Magento\Framework\Event\Manager::class)
            ->setMethods(['dispatch'])->disableOriginalConstructor()->getMock();
        $eventManager->expects($this->any())->method('dispatch')->will($this->returnSelf());
        $title = $this->getMockBuilder(\Magento\Framework\App\Action\Title::class)
            ->setMethods(['add'])->disableOriginalConstructor()->getMock();
        $title->expects($this->any())->method('prepend')->withAnyParameters()->will($this->returnSelf());
        $requestInterfaceMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)->setMethods(
            ['getParam', 'getPost', 'getFullActionName', 'getPostValue']
        )->disableOriginalConstructor()->getMock();

        $responseInterfaceMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)->setMethods(
            ['setRedirect', 'sendResponse']
        )->getMock();

        $managerInterfaceMock = $this->getMock(\Magento\Framework\Message\ManagerInterface::class);
        $sessionMock = $this->getMock(
            \Magento\Backend\Model\Session::class,
            ['getProductData', 'setProductData'],
            [],
            '',
            false
        );
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
                'getResultRedirectFactory',
                'getResultFactory'
            ],
            [],
            '',
            false
        );

        $this->context->expects($this->any())->method('getTitle')->will($this->returnValue($title));
        $this->context->expects($this->any())->method('getEventManager')->will($this->returnValue($eventManager));
        $this->context->expects($this->any())->method('getRequest')->will($this->returnValue($requestInterfaceMock));
        $this->context->expects($this->any())->method('getResponse')->will($this->returnValue($responseInterfaceMock));
        $this->context->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);

        $this->context->expects($this->any())->method('getMessageManager')
            ->will($this->returnValue($managerInterfaceMock));
        $this->context->expects($this->any())->method('getSession')->will($this->returnValue($sessionMock));
        $this->context->expects($this->any())->method('getActionFlag')->will($this->returnValue($actionFlagMock));
        $this->context->expects($this->any())->method('getHelper')->will($this->returnValue($helperDataMock));

        foreach ($additionalParams as $property => $object) {
            $this->context->expects($this->any())->method('get' . ucfirst($property))->willReturn($object);
        }

        $this->session = $sessionMock;
        $this->request = $requestInterfaceMock;

        return $this->context;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Catalog\Controller\Product;
use Magento\Catalog\Model\Product\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Layout;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class ProductTest extends TestCase
{
    /** @var MockObject */
    protected $context;

    /** @var Product */
    protected $action;

    /** @var Layout  */
    protected $layout;

    /** @var Session|MockObject */
    protected $session;

    /** @var Http|MockObject */
    protected $request;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * Init context object
     *
     * @param array $additionalParams
     * @param array $objectManagerMap Object Manager mappings
     * @return MockObject
     */
    protected function initContext(array $additionalParams = [], array $objectManagerMap = [])
    {
        $productActionMock = $this->createMock(Action::class);

        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        if ($objectManagerMap) {
            $this->objectManagerMock->expects($this->any())
                ->method('get')
                ->willReturnMap($objectManagerMap);
        }

        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturn($productActionMock);

        $block = $this->getMockBuilder(AbstractBlock::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->layout = $this->getMockBuilder(Layout::class)
            ->setMethods(['getBlock'])->disableOriginalConstructor()
            ->getMock();
        $this->layout->expects($this->any())->method('getBlock')->willReturn($block);

        $eventManager = $this->getMockBuilder(Manager::class)
            ->setMethods(['dispatch'])->disableOriginalConstructor()
            ->getMock();
        $eventManager->expects($this->any())->method('dispatch')->willReturnSelf();
        $requestInterfaceMock = $this->getMockBuilder(Http::class)
            ->setMethods(
                ['getParam', 'getPost', 'getFullActionName', 'getPostValue']
            )->disableOriginalConstructor()
            ->getMock();

        $responseInterfaceMock = $this->getMockBuilder(ResponseInterface::class)
            ->setMethods(
                ['setRedirect', 'sendResponse']
            )->getMockForAbstractClass();

        $managerInterfaceMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $sessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(['getProductData', 'setProductData'])
            ->disableOriginalConstructor()
            ->getMock();
        $actionFlagMock = $this->createMock(ActionFlag::class);
        $helperDataMock = $this->createMock(Data::class);
        $this->context = $this->getMockBuilder(Context::class)
            ->addMethods(['getTitle'])
            ->onlyMethods(
                [
                    'getRequest',
                    'getResponse',
                    'getObjectManager',
                    'getEventManager',
                    'getMessageManager',
                    'getSession',
                    'getActionFlag',
                    'getHelper',
                    'getView',
                    'getResultRedirectFactory',
                    'getResultFactory'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

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

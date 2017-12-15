<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RouterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cms\Controller\Router
     */
    private $router;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Cms\Model\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageFactoryMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var \Magento\Framework\App\ActionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionFactoryMock;

    protected function setUp()
    {
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->pageFactoryMock = $this->getMockBuilder(\Magento\Cms\Model\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->getMockForAbstractClass();

        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->actionFactoryMock = $this->getMockBuilder(\Magento\Framework\App\ActionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->router = $objectManagerHelper->getObject(
            \Magento\Cms\Controller\Router::class,
            [
                'eventManager' => $this->eventManagerMock,
                'pageFactory' => $this->pageFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'actionFactory' => $this->actionFactoryMock,
            ]
        );
    }

    public function testMatchCmsControllerRouterMatchBeforeEventParams()
    {
        $identifier = '/test';
        $trimedIdentifier = 'test';
        $pageId = 1;
        $storeId = 1;

        /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods([
                'getPathInfo',
                'setModuleName',
                'setControllerName',
                'setActionName',
                'setParam',
                'setAlias',
            ])
            ->getMockForAbstractClass();
        $requestMock->expects($this->once())
            ->method('getPathInfo')
            ->willReturn($identifier);
        $requestMock->expects($this->once())
            ->method('setModuleName')
            ->with('cms')
            ->willReturnSelf();
        $requestMock->expects($this->once())
            ->method('setControllerName')
            ->with('page')
            ->willReturnSelf();
        $requestMock->expects($this->once())
            ->method('setActionName')
            ->with('view')
            ->willReturnSelf();
        $requestMock->expects($this->once())
            ->method('setParam')
            ->with('page_id', $pageId)
            ->willReturnSelf();
        $requestMock->expects($this->once())
            ->method('setAlias')
            ->with(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $trimedIdentifier)
            ->willReturnSelf();

        $condition = new \Magento\Framework\DataObject(['identifier' => $trimedIdentifier, 'continue' => true]);

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'cms_controller_router_match_before',
                [
                    'router' => $this->router,
                    'condition' => $condition,
                ]
            )
            ->willReturnSelf();

        $pageMock = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageMock->expects($this->once())
            ->method('checkIdentifier')
            ->with($trimedIdentifier, $storeId)
            ->willReturn($pageId);

        $this->pageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($pageMock);

        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);

        $actionMock = $this->getMockBuilder(\Magento\Framework\App\ActionInterface::class)
            ->getMockForAbstractClass();

        $this->actionFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\App\Action\Forward::class)
            ->willReturn($actionMock);

        $this->assertEquals($actionMock, $this->router->match($requestMock));
    }
}

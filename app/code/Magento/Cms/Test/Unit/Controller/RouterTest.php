<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Controller;

use Magento\Cms\Controller\Router;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RouterTest extends TestCase
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var PageFactory|MockObject
     */
    private $pageFactoryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var ActionFactory|MockObject
     */
    private $actionFactoryMock;

    protected function setUp(): void
    {
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->pageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->getMockForAbstractClass();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->actionFactoryMock = $this->getMockBuilder(ActionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->router = $objectManagerHelper->getObject(
            Router::class,
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
        $trimmedIdentifier = 'test';
        $pageId = 1;
        $storeId = 1;

        /** @var RequestInterface|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->addMethods([
                'getPathInfo',
                'setControllerName',
                'setParam',
                'setAlias',
            ])
            ->onlyMethods([
                'setModuleName',
                'setActionName'
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
            ->with(Url::REWRITE_REQUEST_PATH_ALIAS, $trimmedIdentifier)
            ->willReturnSelf();

        $condition = new DataObject(['identifier' => $trimmedIdentifier, 'continue' => true]);

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

        $pageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageMock->expects($this->once())
            ->method('checkIdentifier')
            ->with($trimmedIdentifier, $storeId)
            ->willReturn($pageId);

        $this->pageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($pageMock);

        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);

        $actionMock = $this->getMockBuilder(ActionInterface::class)
            ->getMockForAbstractClass();

        $this->actionFactoryMock->expects($this->once())
            ->method('create')
            ->with(Forward::class)
            ->willReturn($actionMock);

        $this->assertEquals($actionMock, $this->router->match($requestMock));
    }
}

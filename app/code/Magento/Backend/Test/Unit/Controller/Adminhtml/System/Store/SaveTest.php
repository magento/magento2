<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Controller\Adminhtml\System\Store;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\System\Store\Save;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Backend\Controller\Adminhtml\System\Store\Save controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends TestCase
{
    /**
     * @var Save
     */
    private $controller;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ResponseInterface|MockObject
     */
    private $responseMock;

    /**
     * @var ObjectManager|MockObject
     */
    private $objectManagerMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messagesMock;

    /**
     * @var Data|MockObject
     */
    private $helperMock;

    /**
     * @var Session|MockObject
     */
    private $sessionMock;

    /**
     * @var Store|MockObject
     */
    private $storeModelMock;

    /**
     * @var Group|MockObject
     */
    private $groupModelMock;

    /**
     * @var FilterManager|MockObject
     */
    private $filterManagerMock;

    /**
     * @var TypeListInterface|MockObject
     */
    private $cacheTypeListMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var ForwardFactory|MockObject
     */
    private $resultForwardFactoryMock;

    /**
     * @var PageFactory|MockObject
     */
    private $resultPageFactoryMock;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isPost', 'getPostValue'])
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get', 'create'])
            ->getMock();
        $this->messagesMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addSuccessMessage', 'addErrorMessage'])
            ->getMockForAbstractClass();
        $this->helperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUrl'])
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['setPostData'])
            ->getMock();
        $this->storeModelMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'setData', 'setId', 'getGroupId', 'setWebsiteId', 'isActive', 'isDefault', 'save'])
            ->getMock();
        $this->groupModelMock = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getWebsiteId'])
            ->getMock();
        $this->filterManagerMock = $this->getMockBuilder(FilterManager::class)
            ->disableOriginalConstructor()
            ->addMethods(['removeTags'])
            ->getMock();
        $this->cacheTypeListMock = $this->getMockBuilder(TypeListInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['cleanType'])
            ->getMockForAbstractClass();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $redirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setPath', 'setUrl'])
            ->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->willReturnSelf();
        $redirectFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultRedirect);
        $contextMock = $this->getMockBuilder(Context::class)
            ->onlyMethods([
                'getRequest',
                'getResponse',
                'getObjectManager',
                'getHelper',
                'getMessageManager',
                'getResultRedirectFactory'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->once())->method('getResponse')->willReturn($this->responseMock);
        $contextMock->expects($this->once())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $contextMock->expects($this->once())->method('getHelper')->willReturn($this->helperMock);
        $contextMock->expects($this->once())->method('getMessageManager')->willReturn($this->messagesMock);
        $contextMock->expects($this->once())->method('getResultRedirectFactory')->willReturn($redirectFactory);
        $this->controller = new Save(
            $contextMock,
            $this->registryMock,
            $this->filterManagerMock,
            $this->resultForwardFactoryMock,
            $this->resultPageFactoryMock,
            $this->cacheTypeListMock
        );
    }

    /**
     * Test saving a store view
     */
    public function testSaveAction(): void
    {
        $storeId = 2;
        $requestParams = [
            'store_type' => 'store',
            'store_action' => 'edit',
            'store' => [
                'store_id' => $storeId,
                'name' => 'Test Store View',
                'code' => 'test_store',
                'is_active' => 1,
                'group_id' => 1
            ]
        ];
        $this->requestMock->expects($this->once())->method('isPost')->willReturn(true);
        $this->requestMock->expects($this->once())->method('getPostValue')->willReturn($requestParams);
        $this->filterManagerMock->expects($this->once())
            ->method('removeTags')
            ->with($requestParams['store']['name'])
            ->willReturn($requestParams['store']['name']);
        $this->objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap([
                [Store::class, $this->storeModelMock],
                [Group::class, $this->groupModelMock]
            ]);
        $this->storeModelMock->expects($this->once())->method('load')->with($storeId)->willReturnSelf();
        $this->storeModelMock->expects($this->once())->method('setData')->willReturnSelf();
        $this->storeModelMock->expects($this->never())->method('setId');
        $this->storeModelMock->expects($this->once())->method('getGroupId')->willReturn(1);
        $this->storeModelMock->expects($this->once())->method('isActive')->willReturn(true);
        $this->storeModelMock->expects($this->once())->method('save')->willReturnSelf();
        $this->groupModelMock->expects($this->once())->method('load')->willReturnSelf();
        $this->groupModelMock->expects($this->once())->method('getWebsiteId')->willReturn(1);
        $this->storeModelMock->expects($this->once())->method('setWebsiteId')->willReturnSelf();
        $this->cacheTypeListMock->expects($this->once())->method('cleanType')->with('config');
        $this->messagesMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You saved the store view.'));
        $this->controller->execute();
    }
}

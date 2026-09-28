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
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as HelperObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
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
    use MockCreationTrait;

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
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->objectManagerHelper = new HelperObjectManager($this);
        $this->requestMock = $this->createPartialMock(Http::class, ['isPost', 'getPostValue']);
        $this->responseMock = $this->createMock(ResponseHttp::class);
        $this->objectManagerMock = $this->createPartialMock(ObjectManager::class, ['get', 'create']);
        $this->messagesMock = $this->createMock(ManagerInterface::class);
        $this->helperMock = $this->createPartialMock(Data::class, ['getUrl']);
        $this->sessionMock = $this->createPartialMockWithReflection(
            Session::class,
            ['setPostData']
        );
        $this->storeModelMock = $this->createPartialMock(
            Store::class,
            ['load', 'setData', 'setId', 'getGroupId', 'setWebsiteId', 'isActive', 'isDefault', 'save']
        );
        $this->groupModelMock = $this->createPartialMock(Group::class, ['load', 'getWebsiteId']);
        $this->filterManagerMock = $this->createPartialMockWithReflection(
            FilterManager::class,
            ['removeTags']
        );
        $this->cacheTypeListMock = $this->createMock(TypeListInterface::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->resultForwardFactoryMock = $this->createMock(ForwardFactory::class);
        $this->resultPageFactoryMock = $this->createMock(PageFactory::class);
        $redirectFactory = $this->createPartialMock(RedirectFactory::class, ['create']);
        $resultRedirect = $this->createPartialMock(Redirect::class, ['setPath', 'setUrl']);
        $resultRedirect->expects($this->once())->method('setPath')->willReturnSelf();
        $redirectFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultRedirect);
        $contextMock = $this->createPartialMock(
            Context::class,
            [
                'getRequest',
                'getResponse',
                'getObjectManager',
                'getHelper',
                'getMessageManager',
                'getResultRedirectFactory'
            ]
        );
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

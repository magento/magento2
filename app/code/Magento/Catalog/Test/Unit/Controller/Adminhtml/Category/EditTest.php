<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category;

use Magento\Framework\Registry;
use Magento\Cms\Model\Wysiwyg\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Store\Model\Store;
use Magento\Catalog\Controller\Adminhtml\Category\Edit;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManager;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page as ResultPage;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class EditTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\PageFactory|MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Page|MockObject
     */
    protected $resultPageMock;

    /**
     * @var JsonFactory|MockObject
     */
    protected $resultJsonFactoryMock;

    /**
     * @var LayoutFactory|MockObject
     */
    protected $storeManagerInterfaceMock;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Title|MockObject
     */
    protected $titleMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $responseMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Edit
     */
    protected $edit;

    /**
     * @var Session|MockObject
     */
    protected $sessionMock;

    /**
     * @var Category|MockObject
     */
    protected $categoryMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * Set up
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $objects = [
            [
                StoreManagerInterface::class,
                $this->createMock(StoreManagerInterface::class)
            ],
            [
                Registry::class,
                $this->createMock(Registry::class)
            ],
            [
                Config::class,
                $this->createMock(Config::class)
            ],
            [
                \Magento\Backend\Model\Auth\Session::class,
                $this->createMock(\Magento\Backend\Model\Auth\Session::class)
            ]
        ];
        $this->objectManager->prepareObjectManager($objects);

        $this->categoryMock = $this->createPartialMock(
            Category::class,
            [
                'getPath',
                'addData',
                'getId',
                'getName',
                'getResource',
                'setStoreId',
                'toArray'
            ]
        );

        $this->resultRedirectFactoryMock = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );

        $pageConfig = $this->createPartialMock(PageConfig::class, ['getTitle']);
        $pageTitle = $this->createPartialMock(Title::class, ['prepend', 'set']);
        $pageTitle->method('prepend')->willReturnSelf();
        $pageTitle->method('set')->willReturnSelf();
        $pageConfig->method('getTitle')->willReturn($pageTitle);
        
        $this->resultPageMock = $this->createPartialMockWithReflection(
            ResultPage::class,
            ['setActiveMenu', 'getConfig', 'addBreadcrumb']
        );
        $this->resultPageMock->method('setActiveMenu')->willReturnSelf();
        $this->resultPageMock->method('addBreadcrumb')->willReturnSelf();
        $this->resultPageMock->method('getConfig')->willReturn($pageConfig);

        $this->resultPageFactoryMock = $this->createPartialMock(
            PageFactory::class,
            ['create']
        );
        $this->resultPageFactoryMock->method('create')->willReturn($this->resultPageMock);

        $this->resultJsonFactoryMock = $this->createPartialMock(
            JsonFactory::class,
            ['create']
        );
        $this->storeManagerInterfaceMock = $this->createPartialMockWithReflection(
            StoreManager::class,
            ['getStore', 'getDefaultStoreView', 'getRootCategoryId', 'getCode']
        );
        $this->requestMock = $this->createPartialMockWithReflection(
            RequestInterface::class,
            [
                'getParam', 'setParam', 'getQuery',
                'getModuleName', 'setModuleName', 'getActionName', 'setActionName',
                'getCookie', 'getDistroBaseUrl', 'getRequestUri', 'getScheme',
                'setParams', 'getParams', 'isSecure', 'getPost'
            ]
        );
        $this->requestMock->method('setParam')->willReturnSelf();
        $this->requestMock->method('getModuleName')->willReturn('catalog');
        $this->requestMock->method('setModuleName')->willReturnSelf();
        $this->requestMock->method('getActionName')->willReturn('edit');
        $this->requestMock->method('setActionName')->willReturnSelf();
        $this->requestMock->method('getCookie')->willReturn(null);
        $this->requestMock->method('getDistroBaseUrl')->willReturn('');
        $this->requestMock->method('getRequestUri')->willReturn('/');
        $this->requestMock->method('getScheme')->willReturn('http');
        $this->requestMock->method('setParams')->willReturnSelf();
        $this->requestMock->method('getParams')->willReturn([]);
        $this->requestMock->method('isSecure')->willReturn(false);
        $this->requestMock->method('getPost')->willReturn(null);
        $this->requestMock->method('getQuery')->willReturn(null);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->messageManagerMock = $this->createMock(MessageManagerInterface::class);
        $this->titleMock = $this->createMock(Title::class);
        $this->sessionMock = $this->createPartialMockWithReflection(Session::class, ['getCategoryData']);
        $this->sessionMock->method('getCategoryData')->willReturn(null);

        $this->contextMock = $this->createPartialMockWithReflection(Context::class, [
            'getRequest', 'getObjectManager', 'getEventManager', 'getMessageManager',
            'getResultRedirectFactory', 'getTitle', 'getSession'
        ]);
        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->method('getEventManager')->willReturn($this->eventManagerMock);
        $this->contextMock->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->method('getResultRedirectFactory')->willReturn($this->resultRedirectFactoryMock);
        $this->contextMock->method('getTitle')->willReturn($this->titleMock);
        $this->contextMock->method('getSession')->willReturn($this->sessionMock);

        $this->edit = $this->objectManager->getObject(
            Edit::class,
            [
                'context' => $this->contextMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'storeManager' => $this->storeManagerInterfaceMock
            ]
        );
    }

    /**
     * Run test execute method
     *
     * @param int|bool $categoryId
     * @param int $storeId
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    #[DataProvider('dataProviderExecute')]
    public function testExecute($categoryId, $storeId)
    {
        $rootCategoryId = 2;

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', false, $categoryId],
                    ['store', null, $storeId],
                ]
            );
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getQuery')
            ->with('isAjax')
            ->willReturn(false);

        $this->mockInitCategoryCall();

        $this->sessionMock->expects($this->once())
            ->method('getCategoryData')
            ->with(true)
            ->willReturn([]);

        $this->storeManagerInterfaceMock->expects($this->any())
            ->method('getStore')
            ->with($storeId)->willReturnSelf();

        if (!$categoryId) {
            if (!$storeId) {
                $this->storeManagerInterfaceMock->expects($this->once())
                    ->method('getDefaultStoreView')->willReturnSelf();
            }
            $this->storeManagerInterfaceMock->expects($this->once())
                ->method('getRootCategoryId')
                ->willReturn($rootCategoryId);
            $categoryId = $rootCategoryId;
        }

        $this->requestMock->expects($this->atLeastOnce())
            ->method('setParam')
            ->with('id', $categoryId)
            ->willReturn(true);

        $this->categoryMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($categoryId);

        $this->edit->execute();
    }

    /**
     * Data provider for execute
     *
     * @return array
     */
    public static function dataProviderExecute()
    {
        return [
            [
                'categoryId' => null,
                'storeId' => null,
            ],
            [
                'categoryId' => null,
                'storeId' => 7,
            ]
        ];
    }

    /**
     * Mock for method "_initCategory"
     */
    private function mockInitCategoryCall()
    {
        $this->objectManagerMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->categoryMock);
    }
}

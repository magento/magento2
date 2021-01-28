<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category;

/**
 * Class EditTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\PageFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Page|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultJsonFactoryMock;

    /**
     * @var \Magento\Framework\View\LayoutFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerInterfaceMock;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $titleMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Category\Edit
     */
    protected $edit;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Catalog\Model\Category|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $categoryMock;

    /**
     * Set up
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->categoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\Category::class,
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

        $this->contextMock = $this->createPartialMock(
            \Magento\Backend\App\Action\Context::class,
            [
                'getTitle',
                'getRequest',
                'getObjectManager',
                'getEventManager',
                'getResponse',
                'getMessageManager',
                'getResultRedirectFactory',
                'getSession'
            ]
        );

        $this->resultRedirectFactoryMock = $this->createPartialMock(
            \Magento\Backend\Model\View\Result\RedirectFactory::class,
            ['create']
        );

        $this->resultPageMock = $this->createPartialMock(
            \Magento\Framework\View\Result\Page::class,
            ['setActiveMenu', 'getConfig', 'addBreadcrumb', 'getLayout', 'getBlock', 'getTitle', 'prepend']
        );
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturnSelf();
        $this->resultPageMock->expects($this->any())
            ->method('getTitle')
            ->willReturnSelf();

        $this->resultPageFactoryMock = $this->createPartialMock(
            \Magento\Framework\View\Result\PageFactory::class,
            ['create']
        );
        $this->resultPageFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resultPageMock);

        $this->resultJsonFactoryMock = $this->createPartialMock(
            \Magento\Framework\Controller\Result\JsonFactory::class,
            ['create']
        );
        $this->storeManagerInterfaceMock = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getStore', 'getDefaultStoreView', 'getRootCategoryId', 'getCode']
        );
        $this->requestMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getParam', 'getPost', 'getPostValue', 'getQuery', 'setParam']
        );
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Event\ManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );
        $this->sessionMock = $this->createPartialMock(\Magento\Backend\Model\Session::class, ['__call']);

        $this->contextMock->expects($this->any())->method('getTitle')->willReturn($this->titleMock);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())->method('getEventManager')->willReturn($this->eventManagerMock);
        $this->contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects($this->any())->method('getSession')->willReturn($this->sessionMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->edit = $this->objectManager->getObject(
            \Magento\Catalog\Controller\Adminhtml\Category\Edit::class,
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
     * @dataProvider dataProviderExecute
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
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
            ->method('__call')
            ->willReturn([]);

        $this->storeManagerInterfaceMock->expects($this->any())
            ->method('getStore')
            ->with($storeId)
            ->willReturnSelf();

        if (!$categoryId) {
            if (!$storeId) {
                $this->storeManagerInterfaceMock->expects($this->once())
                    ->method('getDefaultStoreView')
                    ->willReturnSelf();
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
    public function dataProviderExecute()
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

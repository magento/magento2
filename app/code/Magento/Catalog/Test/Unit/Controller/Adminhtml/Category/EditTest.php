<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Catalog\Controller\Adminhtml\Category\Edit;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page as ResultPage;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends TestCase
{
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
                \Magento\Store\Model\StoreManagerInterface::class,
                $this->createMock(\Magento\Store\Model\StoreManagerInterface::class)
            ],
            [
                \Magento\Framework\Registry::class,
                $this->createMock(\Magento\Framework\Registry::class)
            ],
            [
                \Magento\Cms\Model\Wysiwyg\Config::class,
                $this->createMock(\Magento\Cms\Model\Wysiwyg\Config::class)
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

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->addMethods(['getTitle'])
            ->onlyMethods(
                [
                    'getRequest',
                    'getObjectManager',
                    'getEventManager',
                    'getResponse',
                    'getMessageManager',
                    'getResultRedirectFactory',
                    'getSession'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactoryMock = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );

        $this->resultPageMock = $this->getMockBuilder(ResultPage::class)
            ->addMethods(['setActiveMenu', 'addBreadcrumb', 'getBlock', 'getTitle', 'prepend'])
            ->onlyMethods(['getConfig', 'getLayout'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')->willReturnSelf();
        $this->resultPageMock->expects($this->any())
            ->method('getTitle')->willReturnSelf();

        $this->resultPageFactoryMock = $this->createPartialMock(
            PageFactory::class,
            ['create']
        );
        $this->resultPageFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resultPageMock);

        $this->resultJsonFactoryMock = $this->createPartialMock(
            JsonFactory::class,
            ['create']
        );
        $this->storeManagerInterfaceMock = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getStore', 'getDefaultStoreView', 'getRootCategoryId', 'getCode']
        );
        $this->requestMock = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getParam', 'getPost', 'getPostValue', 'getQuery', 'setParam']
        );
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->eventManagerMock = $this->getMockForAbstractClass(
            ManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );
        $this->sessionMock = $this->createPartialMock(Session::class, ['__call']);

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

<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Category;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Controller\Category\View;
use Magento\Catalog\Helper\Category;
use Magento\Catalog\Model\Design;
use Magento\Catalog\Model\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ViewTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $response;

    /**
     * @var Category|MockObject
     */
    protected $categoryHelper;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManager;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\View\Layout|MockObject
     */
    protected $layout;

    /**
     * @var ProcessorInterface|MockObject
     */
    protected $update;

    /**
     * @var ViewInterface|MockObject
     */
    protected $view;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var \Magento\Catalog\Model\Category|MockObject
     */
    protected $category;

    /**
     * @var CategoryRepositoryInterface|MockObject
     */
    protected $categoryRepository;

    /**
     * @var Store|MockObject
     */
    protected $store;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var Design|MockObject
     */
    protected $catalogDesign;

    /**
     * @var View
     */
    protected $action;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactory;

    /**
     * @var \Magento\Framework\View\Page|MockObject
     */
    protected $page;

    /**
     * @var Config
     */
    protected $pageConfig;

    /**
     * @var ToolbarMemorizer|MockObject
     */
    protected ToolbarMemorizer $toolbarMemorizer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->addMethods(['setRedirect', 'isRedirect'])
            ->onlyMethods(['sendResponse'])
            ->getMock();

        $this->categoryHelper = $this->createMock(Category::class);
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->eventManager = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->update = $this->getMockForAbstractClass(ProcessorInterface::class);
        $this->layout = $this->createMock(Layout::class);
        $this->layout->expects($this->any())->method('getUpdate')->willReturn($this->update);

        $this->pageConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfig->expects($this->any())->method('addBodyClass')->willReturnSelf();

        $this->page = $this->getMockBuilder(Page::class)
            ->onlyMethods(
                [
                    'getConfig',
                    'initLayout',
                    'addPageLayoutHandles',
                    'getLayout',
                    'addUpdate'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->page->expects($this->any())->method('getConfig')->willReturn($this->pageConfig);
        $this->page->expects($this->any())->method('addPageLayoutHandles')->willReturnSelf();
        $this->page->expects($this->any())->method('getLayout')->willReturn($this->layout);
        $this->page->expects($this->any())->method('addUpdate')->willReturnSelf();

        $this->view = $this->getMockForAbstractClass(ViewInterface::class);
        $this->view->expects($this->any())->method('getLayout')->willReturn($this->layout);

        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->resultFactory->expects($this->any())->method('create')->willReturn($this->page);

        $this->context = $this->createMock(Context::class);
        $this->context->expects($this->any())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->any())->method('getResponse')->willReturn($this->response);
        $this->context->expects($this->any())->method('getObjectManager')
            ->willReturn($this->objectManager);
        $this->context->expects($this->any())->method('getEventManager')->willReturn($this->eventManager);
        $this->context->expects($this->any())->method('getView')->willReturn($this->view);
        $this->context->expects($this->any())->method('getResultFactory')
            ->willReturn($this->resultFactory);
        $this->context->expects($this->once())->method('getRedirect')
            ->willReturn($this->createMock(RedirectInterface::class));

        $this->category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $this->categoryRepository = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);

        $this->store = $this->createMock(Store::class);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($this->store);

        $this->catalogDesign = $this->createMock(Design::class);

        $resultPageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $resultPageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->page);

        $this->toolbarMemorizer = $this->createMock(ToolbarMemorizer::class);

        $this->action = (new ObjectManager($this))->getObject(
            View::class,
            [
                'context' => $this->context,
                'catalogDesign' => $this->catalogDesign,
                'categoryRepository' => $this->categoryRepository,
                'storeManager' => $this->storeManager,
                'resultPageFactory' => $resultPageFactory,
                'categoryHelper' => $this->categoryHelper,
                'toolbarMemorizer' => $this->toolbarMemorizer
            ]
        );
    }

    public function testRedirectOnToolbarAction()
    {
        $categoryId = 123;
        $this->request->expects($this->any())
            ->method('getParams')
            ->willReturn([Toolbar::LIMIT_PARAM_NAME => 12]);
        $this->request->expects($this->any())->method('getParam')->willReturnMap(
            [
                [Action::PARAM_NAME_URL_ENCODED],
                ['id', false, $categoryId]
            ]
        );
        $this->categoryRepository->expects($this->any())->method('get')->with($categoryId)
            ->willReturn($this->category);
        $this->categoryHelper->expects($this->once())->method('canShow')->with($this->category)->willReturn(true);
        $this->toolbarMemorizer->expects($this->once())->method('memorizeParams');
        $this->toolbarMemorizer->expects($this->once())->method('isMemorizingAllowed')->willReturn(true);
        $this->response->expects($this->once())->method('setRedirect');
        $settings = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getPageLayout', 'getLayoutUpdates'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->category
            ->method('hasChildren')
            ->willReturn(true);
        $this->category->expects($this->any())
            ->method('getDisplayMode')
            ->willReturn('products');

        $settings->expects($this->atLeastOnce())->method('getPageLayout')->willReturn('page_layout');
        $settings->expects($this->once())->method('getLayoutUpdates')->willReturn(['update1', 'update2']);
        $this->catalogDesign->expects($this->any())->method('getDesignSettings')->willReturn($settings);

        $this->action->execute();
    }

    /**
     * Apply custom layout update is correct.
     *
     * @param array $expectedData
     *
     * @return void
     * @dataProvider getInvocationData
     */
    public function testApplyCustomLayoutUpdate(array $expectedData): void
    {
        $categoryId = 123;
        $pageLayout = 'page_layout';

        $this->request->expects($this->any())->method('getParam')->willReturnMap(
            [
                [Action::PARAM_NAME_URL_ENCODED],
                ['id', false, $categoryId]
            ]
        );
        $this->request->expects($this->any())
            ->method('getParams')
            ->willReturn([]);

        $this->categoryRepository->expects($this->any())->method('get')->with($categoryId)
            ->willReturn($this->category);

        $this->categoryHelper->expects($this->once())->method('canShow')->with($this->category)->willReturn(true);

        $settings = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getPageLayout', 'getLayoutUpdates'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->category
            ->method('hasChildren')
            ->willReturnCallback(function () use ($expectedData) {
                return $expectedData[1][0]['type'] === 'default';
            });
        $this->category->expects($this->any())
            ->method('getDisplayMode')
            ->willReturn($expectedData[2][0]['displaymode']);
        $this->expectationForPageLayoutHandles($expectedData);
        $settings->expects($this->atLeastOnce())->method('getPageLayout')->willReturn($pageLayout);
        $settings->expects($this->once())->method('getLayoutUpdates')->willReturn(['update1', 'update2']);
        $this->catalogDesign->expects($this->any())->method('getDesignSettings')->willReturn($settings);

        $this->action->execute();
    }

    /**
     * Expected invocation for Layout Handles.
     *
     * @param array $data
     *
     * @return void
     */
    private function expectationForPageLayoutHandles(array $data): void
    {
        $withArgs = [];

        foreach ($data as $expectedData) {
            $withArgs[] = [$expectedData[0], $expectedData[1], $expectedData[2]];
        }
        $this->page
            ->method('addPageLayoutHandles')
            ->willReturnCallback(function (...$withArgs) {
                return null;
            });
    }

    /**
     * Data provider for execute method.
     *
     * @return array
     */
    public static function getInvocationData(): array
    {
        return [
            [
                'expectedData' => [
                    [['type' => 'default'], null, false],
                    [['type' => 'default_without_children'], null, false],
                    [['displaymode' => 'products'], null, false]
                ]
            ],
            [
                'expectedData' => [
                    [['type' => 'default'], null, false],
                    [['type' => 'default_without_children'], null, false],
                    [['displaymode' => 'page'], null, false]
                ]
            ],
            [
                'expectedData' => [
                    [['type' => 'default'], null, false],
                    [['type' => 'default'], null, false],
                    [['displaymode' => 'poducts_and_page'], null, false]
                ]
            ]
        ];
    }
}

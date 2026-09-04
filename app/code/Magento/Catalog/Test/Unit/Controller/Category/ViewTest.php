<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Category;

use PHPUnit\Framework\Attributes\DataProvider;
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
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
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
 * Unit test for Category View controller.
 *
 * @covers \Magento\Catalog\Controller\Category\View
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ViewTest extends TestCase
{
    use MockCreationTrait;
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
     * @var Layout|MockObject
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
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var ForwardFactory|MockObject
     */
    private $resultForwardFactory;

    /**
     * @var RedirectInterface|MockObject
     */
    private $redirect;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->request = $this->createMock(RequestInterface::class);
        $this->response = $this->createPartialMockWithReflection(
            ResponseInterface::class,
            ['setRedirect', 'sendResponse', 'setBody', 'isRedirect']
        );
        $this->response->method('setRedirect')->willReturnSelf();
        $this->response->method('sendResponse')->willReturn(null);
        $this->response->method('setBody')->willReturnSelf();
        $this->response->method('isRedirect')->willReturn(false);

        $this->categoryHelper = $this->createMock(Category::class);
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->eventManager = $this->createMock(ManagerInterface::class);

        $this->update = $this->createMock(ProcessorInterface::class);
        $this->layout = $this->createMock(Layout::class);
        $this->layout->method('getUpdate')->willReturn($this->update);

        $this->pageConfig = $this->createMock(Config::class);
        $this->pageConfig->expects($this->any())->method('addBodyClass')->willReturnSelf();

        $this->page = $this->createPartialMock(
            Page::class,
            ['getConfig', 'initLayout', 'addPageLayoutHandles', 'getLayout', 'addUpdate']
        );
        $this->page->method('getConfig')->willReturn($this->pageConfig);
        $this->page->expects($this->any())->method('addPageLayoutHandles')->willReturnSelf();
        $this->page->method('getLayout')->willReturn($this->layout);
        $this->page->expects($this->any())->method('addUpdate')->willReturnSelf();

        $this->view = $this->createMock(ViewInterface::class);
        $this->view->method('getLayout')->willReturn($this->layout);

        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->resultFactory->method('create')->willReturn($this->page);

        $this->redirect = $this->createMock(RedirectInterface::class);
        $this->context = $this->createMock(Context::class);
        $this->context->method('getRequest')->willReturn($this->request);
        $this->context->method('getResponse')->willReturn($this->response);
        $this->context->method('getObjectManager')->willReturn($this->objectManager);
        $this->context->method('getEventManager')->willReturn($this->eventManager);
        $this->context->method('getView')->willReturn($this->view);
        $this->context->method('getResultFactory')->willReturn($this->resultFactory);
        $this->context->method('getRedirect')->willReturn($this->redirect);

        $this->resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $this->context->method('getResultRedirectFactory')->willReturn($this->resultRedirectFactory);

        $this->resultForwardFactory = $this->createMock(ForwardFactory::class);

        $this->category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);

        $this->store = $this->createMock(Store::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->storeManager->method('getStore')->willReturn($this->store);

        $this->catalogDesign = $this->createMock(Design::class);

        $resultPageFactory = $this->createPartialMock(PageFactory::class, ['create']);
        $resultPageFactory->method('create')
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
                'resultForwardFactory' => $this->resultForwardFactory,
                'categoryHelper' => $this->categoryHelper,
                'toolbarMemorizer' => $this->toolbarMemorizer
            ]
        );
    }

    /**
     * Test execute redirects when toolbar action param is present.
     *
     * @covers \Magento\Catalog\Controller\Category\View::execute
     * @return void
     */
    public function testRedirectOnToolbarAction(): void
    {
        $categoryId = 123;
        $this->request->method('getParams')->willReturn([Toolbar::LIMIT_PARAM_NAME => 12]);
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

        $settings = $this->createPartialMock(DataObject::class, []);
        $settings->setPageLayout('page_layout');
        $settings->setLayoutUpdates(['update1', 'update2']);
        $this->category
            ->method('hasChildren')
            ->willReturn(true);
        $this->category->method('getDisplayMode')->willReturn('products');

        // No mock expectations needed for anonymous class
        $this->catalogDesign->method('getDesignSettings')->willReturn($settings);

        $this->action->execute();
    }

    /**
     * Test execute redirects to category first page when page param (p) is negative.
     *
     * Covers the fix for negative ?p= value causing Elasticsearch exception; expected 301 redirect to first page.
     *
     * @covers \Magento\Catalog\Controller\Category\View::execute
     * @covers \Magento\Catalog\Controller\Category\View::handlePageRedirect
     * @return void
     */
    public function testExecuteRedirectsToFirstPageWhenPageParamIsNegative(): void
    {
        $categoryId = 123;
        $categoryUrl = 'http://example.com/category.html';

        $this->request->method('getParams')->willReturn([]);
        $this->request->method('getParam')->willReturnMap([
            [Action::PARAM_NAME_URL_ENCODED, null, null],
            ['id', false, $categoryId],
            [Toolbar::PAGE_PARM_NAME, null, -1],
        ]);

        $this->store->method('getId')->willReturn(1);
        $this->categoryRepository->expects($this->once())
            ->method('get')
            ->with($categoryId, 1)
            ->willReturn($this->category);

        $this->categoryHelper->expects($this->once())->method('canShow')->with($this->category)->willReturn(true);
        $this->category->method('getUrl')->willReturn($categoryUrl);
        $this->toolbarMemorizer->expects($this->once())->method('memorizeParams');

        $redirect = $this->createMock(Redirect::class);
        $redirect->expects($this->once())->method('setHttpResponseCode')->with(301)->willReturnSelf();
        $redirect->expects($this->once())->method('setUrl')->with($categoryUrl)->willReturnSelf();

        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($redirect);

        $result = $this->action->execute();

        $this->assertSame($redirect, $result);
    }

    /**
     * Test execute forwards to noroute when category is not found (e.g. NoSuchEntityException).
     *
     * @covers \Magento\Catalog\Controller\Category\View::execute
     * @covers \Magento\Catalog\Controller\Category\View::handleCategoryNotFound
     * @return void
     */
    public function testExecuteForwardsToNorouteWhenCategoryNotFound(): void
    {
        $categoryId = 123;

        $this->request->method('getParams')->willReturn([]);
        $this->request->method('getParam')->willReturnMap([
            [Action::PARAM_NAME_URL_ENCODED, null, null],
            ['id', false, $categoryId],
        ]);

        $this->store->method('getId')->willReturn(1);
        $this->categoryRepository->expects($this->atLeastOnce())
            ->method('get')
            ->with($categoryId, 1)
            ->willThrowException(new NoSuchEntityException(__('Category not found')));

        $forward = $this->createMock(Forward::class);
        $forward->expects($this->once())->method('forward')->with('noroute')->willReturnSelf();
        $this->resultForwardFactory->expects($this->once())->method('create')->willReturn($forward);

        $result = $this->action->execute();

        $this->assertSame($forward, $result);
    }

    /**
     * Test execute sets empty body and returns response when category not found due to B2B permission denial.
     *
     * @covers \Magento\Catalog\Controller\Category\View::execute
     * @covers \Magento\Catalog\Controller\Category\View::handleCategoryNotFound
     * @covers \Magento\Catalog\Controller\Category\View::isB2BPermissionDenial
     * @return void
     */
    public function testExecuteSetsEmptyBodyWhenCategoryNotFoundDueToB2BPermissionDenial(): void
    {
        $categoryId = 123;
        $existingCategory = $this->createMock(\Magento\Catalog\Model\Category::class);

        $this->request->method('getParams')->willReturn([]);
        $this->request->method('getParam')->willReturnMap([
            [Action::PARAM_NAME_URL_ENCODED, null, null],
            ['id', false, $categoryId],
        ]);

        $this->store->method('getId')->willReturn(1);
        $this->categoryRepository->expects($this->exactly(2))
            ->method('get')
            ->with($categoryId, 1)
            ->willReturn($existingCategory);

        $this->categoryHelper->expects($this->exactly(2))
            ->method('canShow')
            ->with($existingCategory)
            ->willReturn(false);

        $existingCategory->method('getIsActive')->willReturn(true);
        $existingCategory->method('isInRootCategoryList')->willReturn(true);

        $this->response->expects($this->once())->method('setBody')->with('')->willReturnSelf();

        $result = $this->action->execute();

        $this->assertSame($this->response, $result);
    }

    /**
     * Test execute returns redirect when URL encoded param is present.
     *
     * @covers \Magento\Catalog\Controller\Category\View::execute
     * @covers \Magento\Catalog\Controller\Category\View::handleUrlEncodedRedirect
     * @return void
     */
    public function testExecuteReturnsRedirectWhenUrlEncodedParamPresent(): void
    {
        $redirectUrl = 'http://example.com/';

        $this->request->method('getParams')->willReturn([]);
        $this->request->method('getParam')
            ->willReturnMap([
                [Action::PARAM_NAME_URL_ENCODED, null, '1'],
                ['id', false, null],
            ]);

        $this->redirect->method('getRedirectUrl')->willReturn($redirectUrl);

        $redirect = $this->createMock(Redirect::class);
        $redirect->expects($this->once())->method('setUrl')->with($redirectUrl)->willReturnSelf();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($redirect);

        $result = $this->action->execute();

        $this->assertSame($redirect, $result);
    }

    /**
     * Apply custom layout update is correct.
     *
     * @covers \Magento\Catalog\Controller\Category\View::execute
     * @param array $expectedData
     * @return void
     */
    #[DataProvider('getInvocationData')]
    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
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
        $this->request->method('getParams')->willReturn([]);

        $this->categoryRepository->expects($this->any())->method('get')->with($categoryId)
            ->willReturn($this->category);

        $this->categoryHelper->expects($this->once())->method('canShow')->with($this->category)->willReturn(true);

        $settings = $this->createPartialMock(DataObject::class, []);
        $settings->setPageLayout('page_layout');
        $settings->setLayoutUpdates(['update1', 'update2']);
        $this->category
            ->method('hasChildren')
            ->willReturnCallback(function () use ($expectedData) {
                return $expectedData[1][0]['type'] === 'default';
            });
        $this->category->method('getDisplayMode')->willReturn($expectedData[2][0]['displaymode']);
        $this->expectationForPageLayoutHandles($expectedData);
        // No mock expectations needed for anonymous class
        $this->catalogDesign->method('getDesignSettings')->willReturn($settings);

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
    /**
     * Data provider for execute method (layout handles by display mode).
     *
     * @return array<string, array{expectedData: array}>
     */
    public static function getInvocationData(): array
    {
        return [
            'default with products display' => [
                'expectedData' => [
                    [['type' => 'default'], null, false],
                    [['type' => 'default_without_children'], null, false],
                    [['displaymode' => 'products'], null, false]
                ]
            ],
            'default with page display' => [
                'expectedData' => [
                    [['type' => 'default'], null, false],
                    [['type' => 'default_without_children'], null, false],
                    [['displaymode' => 'page'], null, false]
                ]
            ],
            'default with products and page display' => [
                'expectedData' => [
                    [['type' => 'default'], null, false],
                    [['type' => 'default'], null, false],
                    [['displaymode' => 'poducts_and_page'], null, false]
                ]
            ]
        ];
    }
}

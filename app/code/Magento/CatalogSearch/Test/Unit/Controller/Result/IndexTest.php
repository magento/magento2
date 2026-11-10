<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Controller\Result;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Layer\Search as SearchLayer;
use Magento\Catalog\Model\Layer\SearchFactory as SearchLayerFactory;
use Magento\Catalog\Model\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Session;
use Magento\CatalogSearch\Controller\Result\Index;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Layout\Element as LayoutElement;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Page;
use Magento\Search\Model\PopularSearchTerms;
use Magento\Search\Model\Query;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for CatalogSearch Result Index controller.
 *
 * @covers \Magento\CatalogSearch\Controller\Result\Index
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class IndexTest extends TestCase
{
    /**
     * Response stub that records setRedirect calls for assertion.
     *
     * @var ResponseInterface|MockObject
     */
    private $responseStub;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var RedirectInterface|MockObject
     */
    private $redirect;

    /**
     * @var UrlInterface|MockObject
     */
    private $url;

    /**
     * @var QueryFactory|MockObject
     */
    private $queryFactory;

    /**
     * @var Query|MockObject
     */
    private $query;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Store|MockObject
     */
    private $store;

    /**
     * @var Resolver|MockObject
     */
    private $layerResolver;

    /**
     * @var ToolbarMemorizer|MockObject
     */
    private $toolbarMemorizer;

    /**
     * @var SearchLayerFactory|MockObject
     */
    private $searchLayerFactory;

    /**
     * @var Collection|MockObject
     */
    private $productCollection;

    /**
     * @var int
     */
    private $collectionSize = 0;

    /**
     * @var string
     */
    private $fileLayoutUpdatesXml = '<layouts><handle id="catalogsearch_result_index_noresults"/></layouts>';

    /**
     * @var ProcessorInterface|MockObject
     */
    private $layoutUpdate;

    /**
     * @var Page|MockObject
     */
    private $page;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var ViewInterface|MockObject
     */
    private $view;

    /**
     * @var Index
     */
    private $controller;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->request = $this->createMock(RequestInterface::class);
        $this->responseStub = new class implements ResponseInterface {
            /** @var string */
            public $redirectUrl;
            /** @var bool */
            public $setNoCacheHeadersCalled = false;

            /**
             * @return void
             */
            public function sendResponse(): void
            {
            }

            /**
             * @param string $url
             * @param int $code
             * @return self
             */
            public function setRedirect($url, $code = 302): self
            {
                $this->redirectUrl = $url;
                return $this;
            }
            public function setNoCacheHeaders(): self
            {
                $this->setNoCacheHeadersCalled = true;
                return $this;
            }
        };
        $this->response = $this->responseStub;
        $this->redirect = $this->createMock(RedirectInterface::class);
        $this->url = $this->createMock(UrlInterface::class);

        $this->objectManager = $this->createMock(ObjectManagerInterface::class);

        $this->context = $this->createMock(Context::class);
        $this->context->method('getRequest')->willReturn($this->request);
        $this->context->method('getResponse')->willReturn($this->response);
        $this->context->method('getRedirect')->willReturn($this->redirect);
        $this->context->method('getUrl')->willReturn($this->url);
        $this->context->method('getObjectManager')->willReturn($this->objectManager);

        $this->layoutUpdate = $this->createMock(ProcessorInterface::class);
        $this->layoutUpdate->method('getHandles')->willReturn([]);
        $this->layoutUpdate->method('getFileLayoutUpdatesXml')
            ->willReturnCallback(fn() => new LayoutElement($this->fileLayoutUpdatesXml));
        $layout = $this->createMock(LayoutInterface::class);
        $layout->method('getUpdate')->willReturn($this->layoutUpdate);
        $this->page = $this->createMock(Page::class);
        $this->page->method('initLayout')->willReturnSelf();
        $this->page->method('getLayout')->willReturn($layout);
        $this->view = $this->createMock(ViewInterface::class);
        $this->view->method('getPage')->willReturn($this->page);
        $this->view->method('getLayout')->willReturn($layout);
        $this->context->method('getView')->willReturn($this->view);

        $this->query = $this->createMock(Query::class);
        $this->queryFactory = $this->createMock(QueryFactory::class);
        $this->queryFactory->method('get')->willReturn($this->query);

        $this->store = $this->createMock(Store::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->storeManager->method('getStore')->willReturn($this->store);

        $this->layerResolver = $this->createMock(Resolver::class);

        $catalogSession = $this->createMock(Session::class);
        $this->toolbarMemorizer = $this->createMock(ToolbarMemorizer::class);

        $this->productCollection = $this->createMock(Collection::class);
        $this->productCollection->method('setPageSize')->with(1)->willReturnSelf();
        $this->productCollection->method('getSize')->willReturnCallback(fn() => $this->collectionSize);
        $searchLayer = $this->createMock(SearchLayer::class);
        $searchLayer->method('getProductCollection')->willReturn($this->productCollection);
        $this->searchLayerFactory = $this->createMock(SearchLayerFactory::class);
        $this->searchLayerFactory->method('create')->willReturn($searchLayer);

        $this->controller = new Index(
            $this->context,
            $catalogSession,
            $this->storeManager,
            $this->queryFactory,
            $this->layerResolver,
            $this->toolbarMemorizer,
            $this->searchLayerFactory
        );
    }

    /**
     * Test execute redirects when page param (p) is negative.
     *
     * Covers the fix for negative ?p= value causing Elasticsearch exception; redirects to same URL without p.
     *
     * @covers \Magento\CatalogSearch\Controller\Result\Index::execute
     * @return void
     */
    public function testExecuteRedirectsWhenPageParamIsNegative(): void
    {
        $redirectUrl = 'http://example.com/catalogsearch/result/';

        $this->layerResolver->expects($this->once())
            ->method('create')
            ->with(Resolver::CATALOG_LAYER_SEARCH);

        $this->store->method('getId')->willReturn(1);
        $this->query->method('setStoreId')->with(1)->willReturnSelf();
        $this->query->method('getQueryText')->willReturn('search term');

        $this->request->method('getParam')
            ->with(Toolbar::PAGE_PARM_NAME)
            ->willReturn(-1);

        $this->url->method('getUrl')
            ->with('*/*', ['_current' => true, '_query' => [Toolbar::PAGE_PARM_NAME => null]])
            ->willReturn($redirectUrl);

        $this->controller->execute();

        $this->assertSame($redirectUrl, $this->responseStub->redirectUrl);
    }

    /**
     * Test execute redirects when query text is empty.
     *
     * @covers \Magento\CatalogSearch\Controller\Result\Index::execute
     * @return void
     */
    public function testExecuteRedirectsWhenQueryTextIsEmpty(): void
    {
        $redirectUrl = 'http://example.com/';

        $this->layerResolver->expects($this->once())
            ->method('create')
            ->with(Resolver::CATALOG_LAYER_SEARCH);

        $this->store->method('getId')->willReturn(1);
        $this->query->method('setStoreId')->with(1)->willReturnSelf();
        $this->query->method('getQueryText')->willReturn('');

        $this->redirect->expects($this->once())
            ->method('getRedirectUrl')
            ->willReturn($redirectUrl);

        $this->controller->execute();

        $this->assertSame($redirectUrl, $this->responseStub->redirectUrl);
    }

    /**
     * Test execute redirects when toolbar action param is present (e.g. order, dir, mode, limit).
     *
     * @covers \Magento\CatalogSearch\Controller\Result\Index::execute
     * @covers \Magento\CatalogSearch\Controller\Result\Index::shouldRedirectOnToolbarAction
     * @return void
     */
    public function testExecuteRedirectsWhenToolbarActionPresent(): void
    {
        $redirectUrl = 'http://example.com/catalogsearch/result/?order=name';

        $this->layerResolver->expects($this->once())
            ->method('create')
            ->with(Resolver::CATALOG_LAYER_SEARCH);

        $this->store->method('getId')->willReturn(1);
        $this->query->method('setStoreId')->with(1)->willReturnSelf();
        $this->query->method('getQueryText')->willReturn('search term');

        $this->request->method('getParam')->with(Toolbar::PAGE_PARM_NAME)->willReturn(null);
        $this->request->method('getParams')->willReturn([
            'q' => 'search term',
            Toolbar::ORDER_PARAM_NAME => 'name',
        ]);

        $this->toolbarMemorizer->expects($this->once())
            ->method('isMemorizingAllowed')
            ->willReturn(true);

        $this->objectManager->method('get')->willReturn($this->createMock(\Magento\CatalogSearch\Helper\Data::class));

        $this->redirect->expects($this->once())
            ->method('getRedirectUrl')
            ->willReturn($redirectUrl);

        $this->controller->execute();

        $this->assertSame($redirectUrl, $this->responseStub->redirectUrl);
    }

    /**
     * Create a query stub for getCacheableResult / getNotCacheableResult (magic on real Query).
     *
     * @param string $queryText Query text
     * @param int|string|null $numResults Number of results (stored value, may be a DB string or null)
     * @param string|null $redirect Redirect URL or null
     * @return object Query-like stub
     */
    private function createQueryStub(
        string $queryText = 'search term',
        int|string|null $numResults = 0,
        ?string $redirect = null
    ): object {
        return new class($queryText, $numResults, $redirect) {
            /** @var string */
            private string $queryText;
            /** @var int|string|null */
            private int|string|null $numResults;
            /** @var string|null */
            private ?string $redirect;

            /**
             * @param string $queryText
             * @param int|string|null $numResults
             * @param string|null $redirect
             */
            public function __construct(string $queryText, int|string|null $numResults, ?string $redirect)
            {
                $this->queryText = $queryText;
                $this->numResults = $numResults;
                $this->redirect = $redirect;
            }

            public function setStoreId($id): self
            {
                return $this;
            }

            public function getQueryText(): string
            {
                return $this->queryText;
            }

            public function getNumResults(): int|string|null
            {
                return $this->numResults;
            }

            public function getRedirect(): ?string
            {
                return $this->redirect;
            }

            public function saveIncrementalPopularity(): void
            {
            }

            public function setId($id): self
            {
                return $this;
            }

            public function setIsActive($v): self
            {
                return $this;
            }

            public function setIsProcessed($v): self
            {
                return $this;
            }
        };
    }

    /**
     * Test execute uses cacheable result path and redirects when isMinQueryLength is false and query has redirect URL.
     *
     * @covers \Magento\CatalogSearch\Controller\Result\Index::execute
     * @covers \Magento\CatalogSearch\Controller\Result\Index::getCacheableResult
     * @return void
     */
    public function testExecuteGetCacheableResultRedirectsWhenNotMinQueryLengthAndQueryHasRedirect(): void
    {
        $queryText = 'search term';
        $redirectUrl = 'http://example.com/catalogsearch/result/?q=search+term';
        $currentUrl = 'http://example.com/other';
        $queryStub = $this->createQueryStub($queryText, 0, $redirectUrl);

        $queryFactory = $this->createMock(QueryFactory::class);
        $queryFactory->method('get')->willReturn($queryStub);

        $catalogSearchHelper = $this->createMock(\Magento\CatalogSearch\Helper\Data::class);
        $catalogSearchHelper->method('isMinQueryLength')->willReturn(false);

        $popularSearchTerms = $this->createMock(PopularSearchTerms::class);
        $popularSearchTerms->method('isCacheable')->with($queryText, 1)->willReturn(true);

        $this->objectManager->method('get')->willReturnMap([
            [\Magento\CatalogSearch\Helper\Data::class, $catalogSearchHelper],
            [PopularSearchTerms::class, $popularSearchTerms],
        ]);

        $this->request->method('getParam')->with(Toolbar::PAGE_PARM_NAME)->willReturn(null);
        $this->request->method('getParams')->willReturn([QueryFactory::QUERY_VAR_NAME => $queryText]);
        $this->toolbarMemorizer->method('isMemorizingAllowed')->willReturn(false);
        $this->store->method('getId')->willReturn(1);

        $this->url->method('getCurrentUrl')->willReturn($currentUrl);

        $controller = new Index(
            $this->context,
            $this->createMock(Session::class),
            $this->storeManager,
            $queryFactory,
            $this->layerResolver,
            $this->toolbarMemorizer,
            $this->searchLayerFactory
        );
        $controller->execute();

        $this->assertSame($redirectUrl, $this->responseStub->redirectUrl);
    }

    /**
     * Test execute uses cacheable result path when no extra params and query is cacheable.
     *
     * @covers \Magento\CatalogSearch\Controller\Result\Index::execute
     * @covers \Magento\CatalogSearch\Controller\Result\Index::getCacheableResult
     * @return void
     */
    public function testExecuteUsesCacheableResultPathWhenNoExtraParamsAndQueryCacheable(): void
    {
        $queryText = 'search term';
        $queryStub = $this->createQueryStub($queryText, 0, null);

        $queryFactory = $this->createMock(QueryFactory::class);
        $queryFactory->method('get')->willReturn($queryStub);

        $catalogSearchHelper = $this->createMock(\Magento\CatalogSearch\Helper\Data::class);
        $catalogSearchHelper->method('isMinQueryLength')->willReturn(true);
        $catalogSearchHelper->expects($this->once())->method('checkNotes');

        $popularSearchTerms = $this->createMock(PopularSearchTerms::class);
        $popularSearchTerms->method('isCacheable')->with($queryText, 1)->willReturn(true);

        $this->objectManager->method('get')->willReturnMap([
            [\Magento\CatalogSearch\Helper\Data::class, $catalogSearchHelper],
            [PopularSearchTerms::class, $popularSearchTerms],
        ]);

        $this->request->method('getParam')->with(Toolbar::PAGE_PARM_NAME)->willReturn(null);
        $this->request->method('getParams')->willReturn([QueryFactory::QUERY_VAR_NAME => $queryText]);
        $this->toolbarMemorizer->method('isMemorizingAllowed')->willReturn(false);
        $this->store->method('getId')->willReturn(1);

        $this->view->expects($this->once())->method('loadLayout')->with(
            $this->equalTo([Index::DEFAULT_NO_RESULT_HANDLE])
        );
        $this->view->expects($this->once())->method('renderLayout');

        $controller = new Index(
            $this->context,
            $this->createMock(Session::class),
            $this->storeManager,
            $queryFactory,
            $this->layerResolver,
            $this->toolbarMemorizer,
            $this->searchLayerFactory
        );
        $controller->execute();
    }

    /**
     * Test execute uses not cacheable result path when extra params present; setNoCacheHeaders and renderLayout.
     *
     * @covers \Magento\CatalogSearch\Controller\Result\Index::execute
     * @covers \Magento\CatalogSearch\Controller\Result\Index::getNotCacheableResult
     * @return void
     */
    public function testExecuteUsesNotCacheableResultPathWhenExtraParamsPresent(): void
    {
        $queryText = 'search term';
        $queryStub = $this->createQueryStub($queryText, 0, null);

        $queryFactory = $this->createMock(QueryFactory::class);
        $queryFactory->method('get')->willReturn($queryStub);

        $catalogSearchHelper = $this->createMock(\Magento\CatalogSearch\Helper\Data::class);
        $catalogSearchHelper->method('isMinQueryLength')->willReturn(true);
        $catalogSearchHelper->expects($this->once())->method('checkNotes');

        $this->objectManager->method('get')->with(\Magento\CatalogSearch\Helper\Data::class)
            ->willReturn($catalogSearchHelper);

        $this->request->method('getParam')->with(Toolbar::PAGE_PARM_NAME)->willReturn(null);
        $this->request->method('getParams')->willReturn([
            QueryFactory::QUERY_VAR_NAME => $queryText,
            'foo' => 'bar',
        ]);
        $this->toolbarMemorizer->method('isMemorizingAllowed')->willReturn(false);
        $this->store->method('getId')->willReturn(1);

        $this->view->expects($this->once())->method('loadLayout')->with(
            $this->equalTo([Index::DEFAULT_NO_RESULT_HANDLE])
        );
        $this->view->expects($this->once())->method('renderLayout');

        $controller = new Index(
            $this->context,
            $this->createMock(Session::class),
            $this->storeManager,
            $queryFactory,
            $this->layerResolver,
            $this->toolbarMemorizer,
            $this->searchLayerFactory
        );
        $controller->execute();

        $this->assertTrue($this->responseStub->setNoCacheHeadersCalled);
    }

    /**
     * Test getNotCacheableResult path when isMinQueryLength is false (no redirect, then render).
     *
     * @covers \Magento\CatalogSearch\Controller\Result\Index::execute
     * @covers \Magento\CatalogSearch\Controller\Result\Index::getNotCacheableResult
     * @return void
     */
    public function testExecuteGetNotCacheableResultRendersWhenNotMinQueryLengthAndNoRedirect(): void
    {
        $queryText = 'search term';
        $queryStub = $this->createQueryStub($queryText, 0, null);

        $queryFactory = $this->createMock(QueryFactory::class);
        $queryFactory->method('get')->willReturn($queryStub);

        $catalogSearchHelper = $this->createMock(\Magento\CatalogSearch\Helper\Data::class);
        $catalogSearchHelper->method('isMinQueryLength')->willReturn(false);
        $catalogSearchHelper->expects($this->once())->method('checkNotes');

        $this->objectManager->method('get')->with(\Magento\CatalogSearch\Helper\Data::class)
            ->willReturn($catalogSearchHelper);

        $this->request->method('getParam')->with(Toolbar::PAGE_PARM_NAME)->willReturn(null);
        $this->request->method('getParams')->willReturn([
            QueryFactory::QUERY_VAR_NAME => $queryText,
            'foo' => 'bar',
        ]);
        $this->toolbarMemorizer->method('isMemorizingAllowed')->willReturn(false);
        $this->store->method('getId')->willReturn(1);

        $this->view->expects($this->once())->method('loadLayout')->with(
            $this->equalTo([Index::DEFAULT_NO_RESULT_HANDLE])
        );
        $this->view->expects($this->once())->method('renderLayout');

        $controller = new Index(
            $this->context,
            $this->createMock(Session::class),
            $this->storeManager,
            $queryFactory,
            $this->layerResolver,
            $this->toolbarMemorizer,
            $this->searchLayerFactory
        );
        $controller->execute();

        $this->assertTrue($this->responseStub->setNoCacheHeadersCalled);
    }

    /**
     * Run execute() through the cacheable (no extra params) or not cacheable (extra params) result path.
     *
     * @param object $queryStub
     * @param bool $cacheable
     * @param bool $isMinQueryLength
     * @return void
     */
    private function executeSearch(
        object $queryStub,
        bool $cacheable = true,
        bool $isMinQueryLength = true
    ): void {
        $queryText = $queryStub->getQueryText();
        $queryFactory = $this->createMock(QueryFactory::class);
        $queryFactory->method('get')->willReturn($queryStub);

        $catalogSearchHelper = $this->createMock(\Magento\CatalogSearch\Helper\Data::class);
        $catalogSearchHelper->method('isMinQueryLength')->willReturn($isMinQueryLength);
        $popularSearchTerms = $this->createMock(PopularSearchTerms::class);
        $popularSearchTerms->method('isCacheable')->willReturn(true);
        $this->objectManager->method('get')->willReturnMap([
            [\Magento\CatalogSearch\Helper\Data::class, $catalogSearchHelper],
            [PopularSearchTerms::class, $popularSearchTerms],
        ]);

        $params = [QueryFactory::QUERY_VAR_NAME => $queryText];
        if (!$cacheable) {
            $params['foo'] = 'bar';
        }
        $this->request->method('getParam')->with(Toolbar::PAGE_PARM_NAME)->willReturn(null);
        $this->request->method('getParams')->willReturn($params);
        $this->toolbarMemorizer->method('isMemorizingAllowed')->willReturn(false);
        $this->store->method('getId')->willReturn(1);

        $controller = new Index(
            $this->context,
            $this->createMock(Session::class),
            $this->storeManager,
            $queryFactory,
            $this->layerResolver,
            $this->toolbarMemorizer,
            $this->searchLayerFactory
        );
        $controller->execute();
    }

    /**
     * Stored num_results > 0: no layer is built, no collection is counted and the layout gets null handles.
     *
     * @covers \Magento\CatalogSearch\Controller\Result\Index::execute
     * @param bool $cacheable
     * @dataProvider resultPathDataProvider
     * @return void
     */
    public function testExecuteSkipsCountWhenStoredNumResultsPositive(bool $cacheable): void
    {
        $this->searchLayerFactory->expects($this->never())->method('create');
        $this->productCollection->expects($this->never())->method('getSize');
        $this->view->expects($this->once())->method('loadLayout')->with($this->isNull());
        $this->view->expects($this->once())->method('renderLayout');

        $this->executeSearch($this->createQueryStub('search term', 5), $cacheable);
    }

    /**
     * Stored num_results is 0/absent and the real collection is empty: no results handle is added.
     *
     * @covers \Magento\CatalogSearch\Controller\Result\Index::execute
     * @param int|string|null $storedNumResults
     * @param bool $cacheable
     * @dataProvider noStoredResultsDataProvider
     * @return void
     */
    public function testExecuteAddsNoResultsHandleWhenCollectionIsEmpty($storedNumResults, bool $cacheable): void
    {
        $this->productCollection->expects($this->once())->method('getSize');
        $this->view->expects($this->once())->method('loadLayout')->with(
            $this->equalTo([Index::DEFAULT_NO_RESULT_HANDLE])
        );

        $this->executeSearch($this->createQueryStub('search term', $storedNumResults), $cacheable);
    }

    /**
     * Stored num_results is 0/absent but the collection has products: handle is not added (stale stored value).
     *
     * @covers \Magento\CatalogSearch\Controller\Result\Index::execute
     * @param int|string|null $storedNumResults
     * @param bool $cacheable
     * @dataProvider noStoredResultsDataProvider
     * @return void
     */
    public function testExecuteDoesNotAddNoResultsHandleWhenCollectionHasProducts(
        $storedNumResults,
        bool $cacheable
    ): void {
        $this->collectionSize = 7;
        $this->productCollection->expects($this->once())->method('getSize');
        $this->view->expects($this->once())->method('loadLayout')->with($this->isNull());

        $this->executeSearch($this->createQueryStub('search term', $storedNumResults), $cacheable);
    }

    /**
     * No layout defines the no results handle: nothing is counted and the layout gets null handles.
     *
     * @covers \Magento\CatalogSearch\Controller\Result\Index::execute
     * @param int|string|null $storedNumResults
     * @param bool $cacheable
     * @dataProvider noStoredResultsDataProvider
     * @return void
     */
    public function testExecuteSkipsCountWhenNoResultsHandleIsNotDefined($storedNumResults, bool $cacheable): void
    {
        $this->fileLayoutUpdatesXml = '<layouts/>';
        $this->searchLayerFactory->expects($this->never())->method('create');
        $this->productCollection->expects($this->never())->method('getSize');
        $this->view->expects($this->once())->method('loadLayout')->with($this->isNull());
        $this->view->expects($this->once())->method('renderLayout');

        $this->executeSearch($this->createQueryStub('search term', $storedNumResults), $cacheable);
    }

    /**
     * No layout defines the no results handle: the default layout handles are not initialized by the controller.
     *
     * @covers \Magento\CatalogSearch\Controller\Result\Index::execute
     * @return void
     */
    public function testExecuteDoesNotInitLayoutWhenNoResultsHandleIsNotDefined(): void
    {
        $this->fileLayoutUpdatesXml = '<layouts/>';
        $this->page->expects($this->never())->method('initLayout');
        $this->layoutUpdate->expects($this->never())->method('getHandles');

        $this->executeSearch($this->createQueryStub('search term', 0));
    }

    /**
     * The no results handle is defined by a layout file: the stored value is checked as before.
     *
     * @covers \Magento\CatalogSearch\Controller\Result\Index::execute
     * @return void
     */
    public function testExecuteInitializesLayoutAndAddsHandleWhenNoResultsHandleIsDefined(): void
    {
        $this->page->expects($this->once())->method('initLayout');
        $this->layoutUpdate->expects($this->once())->method('getHandles');
        $this->view->expects($this->once())->method('loadLayout')->with(
            $this->equalTo([Index::DEFAULT_NO_RESULT_HANDLE])
        );

        $this->executeSearch($this->createQueryStub('search term', 0));
    }

    /**
     * Stored num_results is read from DB as a string; it is cast before comparing.
     *
     * @covers \Magento\CatalogSearch\Controller\Result\Index::execute
     * @return void
     */
    public function testExecuteCastsStoredStringNumResultsToInt(): void
    {
        $this->searchLayerFactory->expects($this->never())->method('create');
        $this->view->expects($this->once())->method('loadLayout')->with($this->isNull());

        $this->executeSearch($this->createQueryStub('search term', '12'));
    }

    /**
     * Term redirect happens before the layout is loaded, so results are never counted.
     *
     * @covers \Magento\CatalogSearch\Controller\Result\Index::execute
     * @param bool $cacheable
     * @dataProvider resultPathDataProvider
     * @return void
     */
    public function testExecuteDoesNotCountResultsOnTermRedirect(bool $cacheable): void
    {
        $redirectUrl = 'http://example.com/redirect-target';
        $this->url->method('getCurrentUrl')->willReturn('http://example.com/other');
        $this->searchLayerFactory->expects($this->never())->method('create');
        $this->view->expects($this->never())->method('loadLayout');

        $this->executeSearch(
            $this->createQueryStub('search term', 0, $redirectUrl),
            $cacheable,
            false
        );

        $this->assertSame($redirectUrl, $this->responseStub->redirectUrl);
    }

    /**
     * Toolbar redirect happens before the layout is loaded, so results are never counted.
     *
     * @covers \Magento\CatalogSearch\Controller\Result\Index::execute
     * @return void
     */
    public function testExecuteDoesNotCountResultsOnToolbarRedirect(): void
    {
        $redirectUrl = 'http://example.com/catalogsearch/result/?order=name';
        $this->store->method('getId')->willReturn(1);
        $this->query->method('setStoreId')->willReturnSelf();
        $this->query->method('getQueryText')->willReturn('search term');
        $this->request->method('getParam')->with(Toolbar::PAGE_PARM_NAME)->willReturn(null);
        $this->request->method('getParams')->willReturn(['q' => 'search term', Toolbar::ORDER_PARAM_NAME => 'name']);
        $this->toolbarMemorizer->method('isMemorizingAllowed')->willReturn(true);
        $this->objectManager->method('get')->willReturn($this->createMock(\Magento\CatalogSearch\Helper\Data::class));
        $this->redirect->method('getRedirectUrl')->willReturn($redirectUrl);
        $this->searchLayerFactory->expects($this->never())->method('create');
        $this->view->expects($this->never())->method('loadLayout');

        $this->controller->execute();

        $this->assertSame($redirectUrl, $this->responseStub->redirectUrl);
    }

    /**
     * @return array
     */
    public static function resultPathDataProvider(): array
    {
        return [
            'cacheable path' => [true],
            'not cacheable path' => [false],
        ];
    }

    /**
     * @return array
     */
    public static function noStoredResultsDataProvider(): array
    {
        $data = [];
        foreach ([true, false] as $cacheable) {
            $path = $cacheable ? 'cacheable' : 'not cacheable';
            $data["int 0, $path"] = [0, $cacheable];
            $data["string '0', $path"] = ['0', $cacheable];
            $data["no stored row (null), $path"] = [null, $cacheable];
        }
        return $data;
    }
}

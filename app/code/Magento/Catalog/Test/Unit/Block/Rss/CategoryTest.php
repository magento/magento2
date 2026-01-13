<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Rss;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Rss\Category;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\Tree;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Framework\Config\View;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\ConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Category
     */
    protected $block;

    /**
     * @var Context|MockObject
     */
    protected $httpContext;

    /**
     * @var Data|MockObject
     */
    protected $catalogHelper;

    /**
     * @var MockObject
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Catalog\Model\Rss\Category|MockObject
     */
    protected $rssModel;

    /**
     * @var UrlBuilderInterface|MockObject
     */
    protected $rssUrlBuilder;

    /**
     * @var Image|MockObject
     */
    protected $imageHelper;

    /**
     * @var Session|MockObject
     */
    protected $customerSession;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfig;

    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var CategoryRepositoryInterface|MockObject
     */
    protected $categoryRepository;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $viewConfig;

    /**
     * @var View
     */
    protected $configView;

    /**
     * @var array
     */
    protected $rssFeed = [
        'title' => 'Category Name',
        'description' => 'Category Name',
        'link' => 'http://magento.com/category-name.html',
        'charset' => 'UTF-8',
        'entries' => [
            [
                'title' => 'Product Name',
                'link' => 'http://magento.com/product.html'
            ]
        ]
    ];

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->request = $this->createMock(RequestInterface::class);
        $this->request
            ->method('getParam')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['cid'] => 1,
                ['store_id'] => null
            });

        $this->httpContext = $this->createMock(Context::class);
        $this->catalogHelper = $this->createMock(Data::class);
        $this->categoryFactory = $this->createPartialMock(CategoryFactory::class, ['create']);
        $this->rssModel = $this->createPartialMock(
            \Magento\Catalog\Model\Rss\Category::class,
            ['getProductCollection']
        );
        $this->rssUrlBuilder = $this->createMock(UrlBuilderInterface::class);
        $this->imageHelper = $this->createMock(Image::class);
        $this->customerSession = $this->createPartialMock(Session::class, ['getId']);
        $this->customerSession->method('getId')->willReturn(1);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $store = $this->createPartialMock(Store::class, ['getId', 'getRootCategoryId']);
        $store->method('getId')->willReturn(1);
        $store->method('getRootCategoryId')->willReturn(1);
        $this->storeManager->method('getStore')->willReturn($store);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->viewConfig = $this->createMock(ConfigInterface::class);
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $objectManagerHelper->getObject(
            Category::class,
            [
                'request' => $this->request,
                'scopeConfig' => $this->scopeConfig,
                'httpContext' => $this->httpContext,
                'catalogData' => $this->catalogHelper,
                'categoryFactory' => $this->categoryFactory,
                'rssModel' => $this->rssModel,
                'rssUrlBuilder' => $this->rssUrlBuilder,
                'imageHelper' => $this->imageHelper,
                'customerSession' => $this->customerSession,
                'storeManager' => $this->storeManager,
                'categoryRepository' => $this->categoryRepository,
                'viewConfig' => $this->viewConfig
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetRssData(): void
    {
        $category = $this->createPartialMock(
            \Magento\Catalog\Model\Category::class,
            ['__sleep', 'load', 'getId', 'getUrl', 'getName']
        );
        $category->expects($this->once())->method('getName')->willReturn('Category Name');
        $category->expects($this->once())->method('getUrl')
            ->willReturn('http://magento.com/category-name.html');

        $this->categoryRepository->expects($this->once())->method('get')->willReturn($category);

        $configViewMock = $this->createMock(View::class);

        $this->viewConfig->expects($this->once())
            ->method('getViewConfig')
            ->willReturn($configViewMock);

        $product = $this->createPartialMockWithReflection(
            Product::class,
            ['getName', 'getProductUrl', 'getAllowedInRss', 'getDescription', 'getAllowedPriceInRss']
        );
        $product->expects($this->once())->method('getName')->willReturn('Product Name');
        $product->expects($this->once())->method('getAllowedInRss')->willReturn(true);
        $product->expects($this->exactly(2))->method('getProductUrl')
            ->willReturn('http://magento.com/product.html');
        $product->expects($this->once())->method('getDescription')
            ->willReturn('Product Description');
        $product->expects($this->once())->method('getAllowedPriceInRss')->willReturn(true);

        $this->rssModel->expects($this->once())->method('getProductCollection')
            ->willReturn([$product]);
        $this->imageHelper->expects($this->once())->method('init')
            ->with($product, 'rss_thumbnail')->willReturnSelf();
        $this->imageHelper->expects($this->once())->method('getUrl')
            ->willReturn('image_link');

        $data = $this->block->getRssData();
        $this->assertEquals($this->rssFeed['link'], $data['link']);
        $this->assertEquals($this->rssFeed['title'], $data['title']);
        $this->assertEquals($this->rssFeed['description'], $data['description']);
        $this->assertEquals($this->rssFeed['entries'][0]['title'], $data['entries'][0]['title']);
        $this->assertEquals($this->rssFeed['entries'][0]['link'], $data['entries'][0]['link']);
        $this->assertStringContainsString(
            '<a href="http://magento.com/product.html">',
            $data['entries'][0]['description']
        );
        $this->assertStringContainsString(
            '<img src="image_link" border="0" align="left" height="75" width="75">',
            $data['entries'][0]['description']
        );

        $this->assertStringContainsString(
            '<td  style="text-decoration:none;">Product Description </td>',
            $data['entries'][0]['description']
        );
    }

    /**
     * @return void
     */
    public function testGetCacheLifetime(): void
    {
        $this->assertEquals(600, $this->block->getCacheLifetime());
    }

    /**
     * @return void
     */
    public function testIsAllowed(): void
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')
            ->with('rss/catalog/category', ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $this->assertTrue($this->block->isAllowed());
    }

    /**
     * @return void
     */
    public function testGetFeeds(): void
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')
            ->with('rss/catalog/category', ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $category = $this->createPartialMock(
            CategoryModel::class,
            ['__sleep', 'getTreeModel', 'getResourceCollection', 'getId', 'getName']
        );

        $collection = $this->createPartialMock(
            Collection::class,
            [
                    'addIdFilter',
                    'addAttributeToSelect',
                    'addAttributeToSort',
                    'load',
                    'addAttributeToFilter',
                    'getIterator'
                ]
        );
        $collection->expects($this->once())->method('addIdFilter')->willReturnSelf();
        $collection->expects($this->exactly(3))->method('addAttributeToSelect')->willReturnSelf();
        $collection->expects($this->once())->method('addAttributeToSort')->willReturnSelf();
        $collection->expects($this->once())->method('addAttributeToFilter')->willReturnSelf();
        $collection->expects($this->once())->method('load')->willReturnSelf();
        $collection->expects($this->once())->method('getIterator')
            ->willReturn(new \ArrayIterator([$category]));
        $category->expects($this->once())->method('getId')->willReturn(1);
        $category->expects($this->once())->method('getName')->willReturn('Category Name');
        $category->expects($this->once())->method('getResourceCollection')->willReturn($collection);
        $this->categoryFactory->expects($this->once())->method('create')->willReturn($category);

        $childNode = new DataObject(['id' => 1]);
        
        $treeNode = $this->createPartialMock(Node::class, ['loadChildren', 'getChildren']);
        $treeNode->expects($this->once())->method('loadChildren')->willReturnSelf();
        $treeNode->expects($this->once())->method('getChildren')->willReturn([$childNode]);

        $tree = $this->createPartialMock(Tree::class, ['loadNode']);
        $tree->expects($this->once())->method('loadNode')->willReturn($treeNode);

        $category->expects($this->once())->method('getTreeModel')->willReturn($tree);

        $this->rssUrlBuilder->expects($this->once())->method('getUrl')
            ->willReturn('http://magento.com/category-name.html');
        $feeds = [
            'group' => 'Categories',
            'feeds' => [
                ['label' => 'Category Name', 'link' => 'http://magento.com/category-name.html'],
            ]
        ];
        $this->assertEquals($feeds, $this->block->getFeeds());
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Rss;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Rss\Category;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\Tree;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
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
                'link' => 'http://magento.com/product.html',
            ],
        ],
    ];

    protected function setUp(): void
    {
        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->request->expects($this->at(0))->method('getParam')->with('cid')->willReturn(1);
        $this->request->expects($this->at(1))->method('getParam')->with('store_id')->willReturn(null);

        $this->httpContext = $this->createMock(Context::class);
        $this->catalogHelper = $this->createMock(Data::class);
        $this->categoryFactory = $this->getMockBuilder(CategoryFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->rssModel = $this->createPartialMock(
            \Magento\Catalog\Model\Rss\Category::class,
            ['getProductCollection']
        );
        $this->rssUrlBuilder = $this->getMockForAbstractClass(UrlBuilderInterface::class);
        $this->imageHelper = $this->createMock(Image::class);
        $this->customerSession = $this->createPartialMock(Session::class, ['getId']);
        $this->customerSession->expects($this->any())->method('getId')->willReturn(1);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $store = $this->getMockBuilder(Store::class)
            ->setMethods(['getId'])->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->categoryRepository = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);
        $this->viewConfig = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();
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
                'viewConfig' => $this->viewConfig,
            ]
        );
    }

    public function testGetRssData()
    {
        $category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->setMethods(['__sleep', 'load', 'getId', 'getUrl', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $category->expects($this->once())->method('getName')->willReturn('Category Name');
        $category->expects($this->once())->method('getUrl')
            ->willReturn('http://magento.com/category-name.html');

        $this->categoryRepository->expects($this->once())->method('get')->willReturn($category);

        $configViewMock = $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewConfig->expects($this->once())
            ->method('getViewConfig')
            ->willReturn($configViewMock);

        $product = $this->getMockBuilder(Product::class)
            ->setMethods(
                [
                    '__sleep',
                    'getName',
                    'getAllowedInRss',
                    'getProductUrl',
                    'getDescription',
                    'getAllowedPriceInRss'
                ]
            )->disableOriginalConstructor()
            ->getMock();
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

    public function testGetCacheLifetime()
    {
        $this->assertEquals(600, $this->block->getCacheLifetime());
    }

    public function testIsAllowed()
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')
            ->with('rss/catalog/category', ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $this->assertTrue($this->block->isAllowed());
    }

    public function testGetFeeds()
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')
            ->with('rss/catalog/category', ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->setMethods(['__sleep', 'getTreeModel', 'getResourceCollection', 'getId', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();

        $collection = $this->getMockBuilder(Collection::class)
            ->setMethods(
                [
                    'addIdFilter',
                    'addAttributeToSelect',
                    'addAttributeToSort',
                    'load',
                    'addAttributeToFilter',
                    'getIterator'
                ]
            )->disableOriginalConstructor()
            ->getMock();
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

        $node = new DataObject(['id' => 1]);
        $nodes = $this->getMockBuilder(Node::class)
            ->setMethods(['getChildren'])->disableOriginalConstructor()
            ->getMock();
        $nodes->expects($this->once())->method('getChildren')->willReturn([$node]);

        $tree = $this->getMockBuilder(Tree::class)
            ->setMethods(['loadChildren', 'loadNode'])->disableOriginalConstructor()
            ->getMock();
        $tree->expects($this->once())->method('loadNode')->willReturnSelf();
        $tree->expects($this->once())->method('loadChildren')->willReturn($nodes);

        $category->expects($this->once())->method('getTreeModel')->willReturn($tree);
        $category->expects($this->once())->method('getResourceCollection')->willReturn('');

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

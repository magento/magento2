<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Block\Rss;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CategoryTest
 * Test for Category
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Block\Rss\Category
     */
    protected $block;

    /**
     * @var \Magento\Framework\App\Http\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $httpContext;

    /**
     * @var \Magento\Catalog\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $catalogHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Catalog\Model\Rss\Category|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rssModel;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rssUrlBuilder;

    /**
     * @var \Magento\Catalog\Helper\Image|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $imageHelper;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Framework\View\ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewConfig;

    /**
     * @var \Magento\Framework\Config\View
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
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->request->expects($this->at(0))->method('getParam')->with('cid')->willReturn(1);
        $this->request->expects($this->at(1))->method('getParam')->with('store_id')->willReturn(null);

        $this->httpContext = $this->createMock(\Magento\Framework\App\Http\Context::class);
        $this->catalogHelper = $this->createMock(\Magento\Catalog\Helper\Data::class);
        $this->categoryFactory = $this->getMockBuilder(\Magento\Catalog\Model\CategoryFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->rssModel = $this->createPartialMock(
            \Magento\Catalog\Model\Rss\Category::class,
            ['getProductCollection']
        );
        $this->rssUrlBuilder = $this->createMock(\Magento\Framework\App\Rss\UrlBuilderInterface::class);
        $this->imageHelper = $this->createMock(\Magento\Catalog\Helper\Image::class);
        $this->customerSession = $this->createPartialMock(\Magento\Customer\Model\Session::class, ['getId']);
        $this->customerSession->expects($this->any())->method('getId')->willReturn(1);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->setMethods(['getId', '__wakeup'])->disableOriginalConstructor()->getMock();
        $store->expects($this->any())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);
        $this->scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->categoryRepository = $this->createMock(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
        $this->viewConfig = $this->getMockBuilder(\Magento\Framework\View\ConfigInterface::class)
            ->getMockForAbstractClass();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $objectManagerHelper->getObject(
            \Magento\Catalog\Block\Rss\Category::class,
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
            ->setMethods(['__sleep', '__wakeup', 'load', 'getId', 'getUrl', 'getName'])
            ->disableOriginalConstructor()->getMock();
        $category->expects($this->once())->method('getName')->willReturn('Category Name');
        $category->expects($this->once())->method('getUrl')
            ->willReturn('http://magento.com/category-name.html');

        $this->categoryRepository->expects($this->once())->method('get')->willReturn($category);

        $configViewMock = $this->getMockBuilder(\Magento\Framework\Config\View::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewConfig->expects($this->once())
            ->method('getViewConfig')
            ->willReturn($configViewMock);

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(
                [
                    '__sleep',
                    '__wakeup',
                    'getName',
                    'getAllowedInRss',
                    'getProductUrl',
                    'getDescription',
                    'getAllowedPriceInRss'
                ]
            )->disableOriginalConstructor()->getMock();
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
            ->with($product, 'rss_thumbnail')
            ->willReturnSelf();
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
            ->with('rss/catalog/category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $this->assertTrue($this->block->isAllowed());
    }

    public function testGetFeeds()
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')
            ->with('rss/catalog/category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->setMethods(['__sleep', '__wakeup', 'getTreeModel', 'getResourceCollection', 'getId', 'getName'])
            ->disableOriginalConstructor()->getMock();

        $collection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Category\Collection::class)
            ->setMethods(
                [
                    'addIdFilter',
                    'addAttributeToSelect',
                    'addAttributeToSort',
                    'load',
                    'addAttributeToFilter',
                    'getIterator'
                ]
            )->disableOriginalConstructor()->getMock();
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

        $node = new \Magento\Framework\DataObject(['id' => 1]);
        $nodes = $this->getMockBuilder(\Magento\Framework\Data\Tree\Node::class)
            ->setMethods(['getChildren'])->disableOriginalConstructor()->getMock();
        $nodes->expects($this->once())->method('getChildren')->willReturn([$node]);

        $tree = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Category\Tree::class)
            ->setMethods(['loadChildren', 'loadNode'])->disableOriginalConstructor()->getMock();
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

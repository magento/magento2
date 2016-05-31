<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Rss;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CategoryTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Rss\Category
     */
    protected $block;

    /**
     * @var \Magento\Framework\App\Http\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpContext;

    /**
     * @var \Magento\Catalog\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Catalog\Model\Rss\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rssModel;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rssUrlBuilder;

    /**
     * @var \Magento\Catalog\Helper\Image|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageHelper;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryRepository;

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

    protected function setUp()
    {
        $this->request = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->request->expects($this->at(0))->method('getParam')->with('cid')->will($this->returnValue(1));
        $this->request->expects($this->at(1))->method('getParam')->with('store_id')->will($this->returnValue(null));

        $this->httpContext = $this->getMock('Magento\Framework\App\Http\Context');
        $this->catalogHelper = $this->getMock('Magento\Catalog\Helper\Data', [], [], '', false);
        $this->categoryFactory = $this->getMockBuilder('Magento\Catalog\Model\CategoryFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->rssModel = $this->getMock('Magento\Catalog\Model\Rss\Category', ['getProductCollection'], [], '', false);
        $this->rssUrlBuilder = $this->getMock('Magento\Framework\App\Rss\UrlBuilderInterface');
        $this->imageHelper = $this->getMock('Magento\Catalog\Helper\Image', [], [], '', false);
        $this->customerSession = $this->getMock('Magento\Customer\Model\Session', ['getId'], [], '', false);
        $this->customerSession->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $store = $this->getMockBuilder('\Magento\Store\Model\Store')
            ->setMethods(['getId', '__wakeup'])->disableOriginalConstructor()->getMock();
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $this->scopeConfig = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->categoryRepository = $this->getMock('Magento\Catalog\Api\CategoryRepositoryInterface');
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $objectManagerHelper->getObject(
            'Magento\Catalog\Block\Rss\Category',
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
            ]
        );
    }

    public function testGetRssData()
    {
        $category = $this->getMockBuilder('\Magento\Catalog\Model\Category')
            ->setMethods(['__sleep', '__wakeup', 'load', 'getId', 'getUrl', 'getName'])
            ->disableOriginalConstructor()->getMock();
        $category->expects($this->once())->method('getName')->will($this->returnValue('Category Name'));
        $category->expects($this->once())->method('getUrl')
            ->will($this->returnValue('http://magento.com/category-name.html'));

        $this->categoryRepository->expects($this->once())->method('get')->will($this->returnValue($category));

        $product = $this->getMockBuilder('\Magento\catalog\Model\Product')
            ->setMethods([
                '__sleep',
                '__wakeup',
                'getName',
                'getAllowedInRss',
                'getProductUrl',
                'getDescription',
                'getAllowedPriceInRss',
            ])->disableOriginalConstructor()->getMock();
        $product->expects($this->once())->method('getName')->will($this->returnValue('Product Name'));
        $product->expects($this->once())->method('getAllowedInRss')->will($this->returnValue(true));
        $product->expects($this->exactly(2))->method('getProductUrl')
            ->will($this->returnValue('http://magento.com/product.html'));
        $product->expects($this->once())->method('getDescription')
            ->will($this->returnValue('Product Description'));
        $product->expects($this->once())->method('getAllowedPriceInRss')->will($this->returnValue(true));

        $this->rssModel->expects($this->once())->method('getProductCollection')
            ->will($this->returnValue([$product]));
        $this->imageHelper->expects($this->once())->method('init')
            ->with($product, 'rss_thumbnail')
            ->will($this->returnSelf());
        $this->imageHelper->expects($this->once())->method('getUrl')
            ->will($this->returnValue('image_link'));

        $data = $this->block->getRssData();
        $this->assertEquals($this->rssFeed['link'], $data['link']);
        $this->assertEquals($this->rssFeed['title'], $data['title']);
        $this->assertEquals($this->rssFeed['description'], $data['description']);
        $this->assertEquals($this->rssFeed['entries'][0]['title'], $data['entries'][0]['title']);
        $this->assertEquals($this->rssFeed['entries'][0]['link'], $data['entries'][0]['link']);
        $this->assertContains('<a href="http://magento.com/product.html">', $data['entries'][0]['description']);
        $this->assertContains(
            '<img src="image_link" border="0" align="left" height="75" width="75">',
            $data['entries'][0]['description']
        );

        $this->assertContains(
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
            ->will($this->returnValue(true));
        $this->assertEquals(true, $this->block->isAllowed());
    }

    public function testGetFeeds()
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')
            ->with('rss/catalog/category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue(true));

        $category = $this->getMockBuilder('\Magento\Catalog\Model\Category')
            ->setMethods(['__sleep', '__wakeup', 'getTreeModel', 'getResourceCollection', 'getId', 'getName'])
            ->disableOriginalConstructor()->getMock();

        $collection = $this->getMockBuilder('\Magento\Catalog\Model\ResourceModel\Category\Collection')
            ->setMethods([
                'addIdFilter',
                'addAttributeToSelect',
                'addAttributeToSort',
                'load',
                'addAttributeToFilter',
                'getIterator',
            ])->disableOriginalConstructor()->getMock();
        $collection->expects($this->once())->method('addIdFilter')->will($this->returnSelf());
        $collection->expects($this->exactly(3))->method('addAttributeToSelect')->will($this->returnSelf());
        $collection->expects($this->once())->method('addAttributeToSort')->will($this->returnSelf());
        $collection->expects($this->once())->method('addAttributeToFilter')->will($this->returnSelf());
        $collection->expects($this->once())->method('load')->will($this->returnSelf());
        $collection->expects($this->once())->method('getIterator')->will($this->returnValue(
            new \ArrayIterator([$category])
        ));

        $category->expects($this->once())->method('getId')->will($this->returnValue(1));
        $category->expects($this->once())->method('getName')->will($this->returnValue('Category Name'));
        $category->expects($this->once())->method('getResourceCollection')->will($this->returnValue($collection));
        $this->categoryFactory->expects($this->once())->method('create')->will($this->returnValue($category));

        $node = new \Magento\Framework\DataObject(['id' => 1]);
        $nodes = $this->getMockBuilder('Magento\Framework\Data\Tree\Node')
            ->setMethods(['getChildren'])->disableOriginalConstructor()->getMock();
        $nodes->expects($this->once())->method('getChildren')->will($this->returnValue([$node]));

        $tree = $this->getMockBuilder('\Magento\Catalog\Model\ResourceModel\Category\Tree')
            ->setMethods(['loadChildren', 'loadNode'])->disableOriginalConstructor()->getMock();
        $tree->expects($this->once())->method('loadNode')->will($this->returnSelf());
        $tree->expects($this->once())->method('loadChildren')->will($this->returnValue($nodes));

        $category->expects($this->once())->method('getTreeModel')->will($this->returnValue($tree));
        $category->expects($this->once())->method('getResourceCollection')->will($this->returnValue(''));

        $this->rssUrlBuilder->expects($this->once())->method('getUrl')
            ->will($this->returnValue('http://magento.com/category-name.html'));
        $feeds = ['group' => 'Categories', 'feeds' => [
            ['label' => 'Category Name', 'link' => 'http://magento.com/category-name.html'],
        ]];
        $this->assertEquals($feeds, $this->block->getFeeds());
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

class CategoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Model\Category */
    protected $category;

    /** @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventManager;

    /** @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $cacheManager;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registry;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var \Magento\Catalog\Model\Resource\Category\Tree|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoryTreeResource;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $categoryTreeFactory;

    /** @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoryRepository;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $storeCollectionFactory;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $url;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $productCollectionFactory;

    /** @var \Magento\Catalog\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $catalogConfig;

    /** @var \Magento\Framework\Filter\FilterManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $filterManager;

    /** @var \Magento\Catalog\Model\Indexer\Category\Flat\State|\PHPUnit_Framework_MockObject_MockObject */
    protected $flatState;

    /** @var \Magento\Indexer\Model\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $flatIndexer;

    /** @var \Magento\Indexer\Model\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $productIndexer;

    /** @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoryUrlPathGenerator;

    /** @var \Magento\UrlRewrite\Model\UrlFinderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlFinder;

    /** @var \Magento\Framework\Model\Resource\AbstractResource|\PHPUnit_Framework_MockObject_MockObject */
    protected $resource;

    /** @var \Magento\Indexer\Model\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $indexerRegistry;

    protected function setUp()
    {
        $this->context = $this->getMock(
            'Magento\Framework\Model\Context',
            ['getEventDispatcher', 'getCacheManager'],
            [],
            '',
            false
        );

        $this->eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $this->context->expects($this->any())->method('getEventDispatcher')
            ->will($this->returnValue($this->eventManager));
        $this->cacheManager = $this->getMock('Magento\Framework\App\CacheInterface');
        $this->context->expects($this->any())->method('getCacheManager')
            ->will($this->returnValue($this->cacheManager));

        $this->registry = $this->getMock('Magento\Framework\Registry');
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->categoryTreeResource = $this->getMock('Magento\Catalog\Model\Resource\Category\Tree', [], [], '', false);
        $this->categoryTreeFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\Category\TreeFactory',
            ['create'],
            [],
            '',
            false);
        $this->categoryRepository = $this->getMock('Magento\Catalog\Api\CategoryRepositoryInterface');
        $this->storeCollectionFactory = $this->getMock(
            'Magento\Store\Model\Resource\Store\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->url = $this->getMock('Magento\Framework\UrlInterface');
        $this->productCollectionFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->catalogConfig = $this->getMock('Magento\Catalog\Model\Config', [], [], '', false);
        $this->filterManager = $this->getMock(
            'Magento\Framework\Filter\FilterManager',
            ['translitUrl'],
            [],
            '',
            false
        );
        $this->flatState = $this->getMock('Magento\Catalog\Model\Indexer\Category\Flat\State', [], [], '', false);
        $this->flatIndexer = $this->getMock('Magento\Indexer\Model\IndexerInterface');
        $this->productIndexer = $this->getMock('Magento\Indexer\Model\IndexerInterface');
        $this->categoryUrlPathGenerator = $this->getMock(
            'Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator',
            [],
            [],
            '',
            false
        );
        $this->urlFinder = $this->getMock('Magento\UrlRewrite\Model\UrlFinderInterface');
        $this->resource = $this->getMock(
            'Magento\Catalog\Model\Resource\Category',
            [],
            [],
            '',
            false
        );
        $this->indexerRegistry = $this->getMock('Magento\Indexer\Model\IndexerRegistry', ['get'], [], '', false);

        $this->category = $this->getCategoryModel();
    }

    public function testFormatUrlKey()
    {
        $strIn = 'Some string';
        $resultString = 'some';

        $this->filterManager->expects($this->once())->method('translitUrl')->with($strIn)
            ->will($this->returnValue($resultString));

        $this->assertEquals($resultString, $this->category->formatUrlKey($strIn));
    }

    /**
     * @expectedException Magento\Framework\Model\Exception
     * @codingStandardsIgnoreStart
     * @expectedExceptionMessage Sorry, but we can't move the category because we can't find the new parent category you selected.
     * @codingStandardsIgnoreEnd
     */
    public function testMoveWhenCannotFindParentCategory()
    {
        $this->markTestIncomplete('MAGETWO-31165');
        $parentCategory = $this->getMock(
            'Magento\Catalog\Model\Category',
            ['getId', 'setStoreId', 'load'],
            [],
            '',
            false
        );
        $parentCategory->expects($this->any())->method('setStoreId')->will($this->returnSelf());
        $parentCategory->expects($this->any())->method('load')->will($this->returnSelf());
        $this->categoryRepository->expects($this->any())->method('get')->will($this->returnValue($parentCategory));

        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->category->move(1, 2);
    }

    /**
     * @expectedException Magento\Framework\Model\Exception
     * @codingStandardsIgnoreStart
     * @expectedExceptionMessage Sorry, but we can't move the category because we can't find the new category you selected.
     * @codingStandardsIgnoreEnd
     */
    public function testMoveWhenCannotFindNewCategory()
    {
        $parentCategory = $this->getMock(
            'Magento\Catalog\Model\Category',
            ['getId', 'setStoreId', 'load'],
            [],
            '',
            false
        );
        $parentCategory->expects($this->any())->method('getId')->will($this->returnValue(5));
        $parentCategory->expects($this->any())->method('setStoreId')->will($this->returnSelf());
        $parentCategory->expects($this->any())->method('load')->will($this->returnSelf());
        $this->categoryRepository->expects($this->any())->method('get')->will($this->returnValue($parentCategory));

        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->category->move(1, 2);
    }

    /**
     * @expectedException Magento\Framework\Model\Exception
     * @codingStandardsIgnoreStart
     * @expectedExceptionMessage We can't perform this category move operation because the parent category matches the child category.
     * @codingStandardsIgnoreEnd
     */
    public function testMoveWhenParentCategoryIsSameAsChildCategory()
    {
        $this->markTestIncomplete('MAGETWO-31165');
        $parentCategory = $this->getMock(
            'Magento\Catalog\Model\Category',
            ['getId', 'setStoreId', 'load'],
            [],
            '',
            false
        );
        $parentCategory->expects($this->any())->method('getId')->will($this->returnValue(5));
        $parentCategory->expects($this->any())->method('setStoreId')->will($this->returnSelf());
        $parentCategory->expects($this->any())->method('load')->will($this->returnSelf());
        $this->categoryRepository->expects($this->any())->method('get')->will($this->returnValue($parentCategory));

        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->category->setId(5);
        $this->category->move(1, 2);
    }

    public function testMovePrimaryWorkflow()
    {
        $indexer = $this->getMock('stdClass', ['isScheduled']);
        $indexer->expects($this->once())->method('isScheduled')->will($this->returnValue(true));
        $this->indexerRegistry->expects($this->once())
            ->method('get')
            ->with('catalog_category_product')
            ->will($this->returnValue($indexer));
        $parentCategory = $this->getMock(
            'Magento\Catalog\Model\Category',
            ['getId', 'setStoreId', 'load'],
            [],
            '',
            false
        );
        $parentCategory->expects($this->any())->method('getId')->will($this->returnValue(5));
        $parentCategory->expects($this->any())->method('setStoreId')->will($this->returnSelf());
        $parentCategory->expects($this->any())->method('load')->will($this->returnSelf());
        $this->categoryRepository->expects($this->any())->method('get')->will($this->returnValue($parentCategory));

        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->category->setId(3);
        $this->category->move(5, 7);
    }

    public function testGetUseFlatResourceFalse()
    {
        $this->assertEquals(false, $this->category->getUseFlatResource());
    }

    public function testGetUseFlatResourceTrue()
    {
        $this->flatState->expects($this->any())
            ->method('isAvailable')
            ->will($this->returnValue(true));

        $category = $this->getCategoryModel();
        $this->assertEquals(true, $category->getUseFlatResource());
    }

    protected function getCategoryModel()
    {
        return (new \Magento\TestFramework\Helper\ObjectManager($this))->getObject(
            'Magento\Catalog\Model\Category',
            [
                'context' => $this->context,
                'registry' => $this->registry,
                'storeManager' => $this->storeManager,
                'categoryTreeResource' => $this->categoryTreeResource,
                'categoryTreeFactory' => $this->categoryTreeFactory,
                'categoryRepository' => $this->categoryRepository,
                'storeCollectionFactory' => $this->storeCollectionFactory,
                'url' => $this->url,
                'productCollectionFactory' => $this->productCollectionFactory,
                'catalogConfig' => $this->catalogConfig,
                'filter' => $this->filterManager,
                'flatState' => $this->flatState,
                'flatIndexer' => $this->flatIndexer,
                'productIndexer' => $this->productIndexer,
                'categoryUrlPathGenerator' => $this->categoryUrlPathGenerator,
                'urlFinder' => $this->urlFinder,
                'resource' => $this->resource,
                'indexerRegistry' => $this->indexerRegistry,
            ]
        );
    }
}

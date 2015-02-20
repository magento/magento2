<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Model\Category;
use Magento\TestFramework\Helper\ObjectManager;

class ProductUrlRewriteGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $canonicalUrlRewriteGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $currentUrlRewritesRegenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $categoriesUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator */
    protected $productUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeViewService;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $product;

    /** @var \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectRegistryFactory;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var \Magento\Catalog\Model\Resource\Category\Collection|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoriesCollection;

    protected function setUp()
    {
        $this->product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->categoriesCollection = $this->getMockBuilder('Magento\Catalog\Model\Resource\Category\Collection')
            ->disableOriginalConstructor()->getMock();
        $this->product->expects($this->any())->method('getCategoryCollection')
            ->will($this->returnValue($this->categoriesCollection));
        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()->getMock();
        $this->categoriesCollection->expects($this->exactly(2))->method('addAttributeToSelect')
            ->will($this->returnSelf());
        $this->currentUrlRewritesRegenerator = $this->getMockBuilder(
            'Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator'
        )->disableOriginalConstructor()->getMock();
        $this->canonicalUrlRewriteGenerator = $this->getMockBuilder(
            'Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator'
        )->disableOriginalConstructor()->getMock();
        $this->categoriesUrlRewriteGenerator = $this->getMockBuilder(
            'Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator'
        )->disableOriginalConstructor()->getMock();
        $this->objectRegistryFactory = $this->getMockBuilder('Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory')
            ->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->storeViewService = $this->getMockBuilder('Magento\CatalogUrlRewrite\Service\V1\StoreViewService')
            ->disableOriginalConstructor()->getMock();

        $this->productUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            'Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator',
            [
                'canonicalUrlRewriteGenerator' => $this->canonicalUrlRewriteGenerator,
                'categoriesUrlRewriteGenerator' => $this->categoriesUrlRewriteGenerator,
                'currentUrlRewritesRegenerator' => $this->currentUrlRewritesRegenerator,
                'objectRegistryFactory' => $this->objectRegistryFactory,
                'storeViewService' => $this->storeViewService,
                'storeManager' => $this->storeManager,
            ]
        );
    }

    public function testGenerationForGlobalScope()
    {
        $this->product->expects($this->any())->method('getStoreId')->will($this->returnValue(null));
        $this->product->expects($this->any())->method('getStoreIds')->will($this->returnValue([1]));
        $this->storeViewService->expects($this->once())->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->will($this->returnValue(false));
        $this->categoriesCollection->expects($this->any())->method('getIterator')
            ->willReturn(new \ArrayIterator([]));
        $this->initObjectRegistryFactory([]);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue(['canonical']));
        $this->categoriesUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue(['categories']));
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->will($this->returnValue(['current']));

        $this->assertEquals(
            ['canonical', 'categories', 'current'],
            $this->productUrlRewriteGenerator->generate($this->product)
        );
    }

    public function testGenerationForSpecificStore()
    {
        $this->product->expects($this->any())->method('getStoreId')->will($this->returnValue(1));
        $this->product->expects($this->never())->method('getStoreIds');
        $storeRootCategoryId = 'root-for-store-id';
        $category = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);
        $category->expects($this->any())->method('getParentIds')
            ->will($this->returnValue(['root-id', $storeRootCategoryId]));
        $category->expects($this->any())->method('getParentId')->will($this->returnValue('parent_id'));
        $category->expects($this->any())->method('getId')->will($this->returnValue('category_id'));
        $store = $this->getMockBuilder('Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $store->expects($this->any())->method('getRootCategoryId')->will($this->returnValue($storeRootCategoryId));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $this->categoriesCollection->expects($this->any())->method('getIterator')
            ->willReturn(new \ArrayIterator([$category]));
        $this->initObjectRegistryFactory([$category]);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue(['canonical']));
        $this->categoriesUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));

        $this->assertEquals(['canonical'], $this->productUrlRewriteGenerator->generate($this->product));
    }

    public function testSkipRootCategoryForCategoriesGenerator()
    {
        $this->product->expects($this->any())->method('getStoreId')->will($this->returnValue(1));
        $this->product->expects($this->never())->method('getStoreIds');
        $store = $this->getMockBuilder('Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $store->expects($this->any())->method('getRootCategoryId')->will($this->returnValue('root-for-store-id'));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $rootCategory = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);
        $rootCategory->expects($this->any())->method('getParentIds')->will($this->returnValue([1, 2]));
        $rootCategory->expects($this->any())->method('getParentId')->will($this->returnValue(Category::TREE_ROOT_ID));
        $this->categoriesCollection->expects($this->any())->method('getIterator')
            ->willReturn(new \ArrayIterator([$rootCategory]));
        $this->initObjectRegistryFactory([]);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue(['canonical']));
        $this->categoriesUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));

        $this->assertEquals(['canonical'], $this->productUrlRewriteGenerator->generate($this->product));
    }

    public function testSkipGenerationForNotStoreRootCategory()
    {
        $this->product->expects($this->any())->method('getStoreId')->will($this->returnValue(1));
        $this->product->expects($this->never())->method('getStoreIds');
        $category = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);
        $category->expects($this->any())->method('getParentIds')
            ->will($this->returnValue(['root-id', 'root-for-store-id']));
        $store = $this->getMockBuilder('Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $store->expects($this->any())->method('getRootCategoryId')->will($this->returnValue('not-root-id'));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $this->categoriesCollection->expects($this->any())->method('getIterator')
            ->willReturn(new \ArrayIterator([$category]));
        $this->initObjectRegistryFactory([]);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue(['canonical']));
        $this->categoriesUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));

        $this->assertEquals(['canonical'], $this->productUrlRewriteGenerator->generate($this->product));
    }

    public function testSkipGenerationForGlobalScope()
    {
        $this->product->expects($this->any())->method('getStoreIds')->will($this->returnValue([1, 2]));
        $this->storeViewService->expects($this->exactly(2))->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->will($this->returnValue(true));

        $this->assertEquals([], $this->productUrlRewriteGenerator->generate($this->product));
    }

    /**
     * @param array $entities
     */
    protected function initObjectRegistryFactory($entities)
    {
        $objectRegistry = $this->getMockBuilder('Magento\CatalogUrlRewrite\Model\ObjectRegistry')
            ->disableOriginalConstructor()->getMock();
        $this->objectRegistryFactory->expects($this->any())->method('create')
            ->with(['entities' => $entities])
            ->will($this->returnValue($objectRegistry));
    }
}

<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\Catalog\Model\Category;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ProductUrlRewriteGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $canonicalUrlRewriteGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $currentUrlRewritesRegenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $categoriesUrlRewriteGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $anchorUrlRewriteGenerator;

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

    /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoriesCollection;

    /**
     * Test method
     */
    protected function setUp()
    {
        $this->product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->categoriesCollection = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Category\Collection')
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
        $this->anchorUrlRewriteGenerator = $this->getMockBuilder(
            'Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator'
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

        $reflection = new \ReflectionClass(get_class($this->productUrlRewriteGenerator));
        $reflectionProperty = $reflection->getProperty('anchorUrlRewriteGenerator');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->productUrlRewriteGenerator, $this->anchorUrlRewriteGenerator);
    }

    /**
     * Test method
     */
    public function testGenerationForGlobalScope()
    {
        $this->product->expects($this->any())->method('getStoreId')->will($this->returnValue(null));
        $this->product->expects($this->any())->method('getStoreIds')->will($this->returnValue([1]));
        $this->storeViewService->expects($this->once())->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->will($this->returnValue(false));
        $this->categoriesCollection->expects($this->any())->method('getIterator')
            ->willReturn(new \ArrayIterator([]));
        $this->initObjectRegistryFactory([]);
        $canonical = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $canonical->setTargetPath('category-1')
            ->setStoreId(1);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$canonical]));
        $categories = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $categories->setTargetPath('category-2')
            ->setStoreId(2);
        $this->categoriesUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$categories]));
        $current = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $current->setTargetPath('category-3')
            ->setStoreId(3);
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$current]));
        $anchorCategories = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $anchorCategories->setTargetPath('category-4')
            ->setStoreId(4);
        $this->anchorUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$anchorCategories]));

        $this->assertEquals(
            [
                'category-1-1' => $canonical,
                'category-2-2' => $categories,
                'category-3-3' => $current,
                'category-4-4' => $anchorCategories
            ],
            $this->productUrlRewriteGenerator->generate($this->product)
        );
    }

    /**
     * Test method
     */
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
        $canonical = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $canonical->setTargetPath('category-1')
            ->setStoreId(1);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$canonical]));
        $this->categoriesUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));
        $this->anchorUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));

        $this->assertEquals(['category-1-1' => $canonical], $this->productUrlRewriteGenerator->generate($this->product));
    }

    /**
     * Test method
     */
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
        $canonical = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $canonical->setTargetPath('category-1')
            ->setStoreId(1);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$canonical]));
        $this->categoriesUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));
        $this->anchorUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));

        $this->assertEquals(['category-1-1' => $canonical], $this->productUrlRewriteGenerator->generate($this->product));
    }

    /**
     * Test method
     */
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
        $canonical = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $canonical->setTargetPath('category-1')
            ->setStoreId(1);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$canonical]));
        $this->categoriesUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));
        $this->anchorUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));

        $this->assertEquals(['category-1-1' => $canonical], $this->productUrlRewriteGenerator->generate($this->product));
    }

    /**
     * Test method
     */
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

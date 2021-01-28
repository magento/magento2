<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class LayerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Layer
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\Category|\PHPUnit\Framework\MockObject\MockObject
     */
    private $category;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit\Framework\MockObject\MockObject
     */
    private $store;

    /**
     * @var \Magento\Catalog\Model\Layer\ContextInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var \Magento\Catalog\Model\Layer\Category\StateKey|\PHPUnit\Framework\MockObject\MockObject
     */
    private $stateKeyGenerator;

    /**
     * @var \Magento\Catalog\Model\Layer\StateFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $stateFactory;

    /**
     * @var \Magento\Catalog\Model\Layer\State|\PHPUnit\Framework\MockObject\MockObject
     */
    private $state;

    /**
     * @var \Magento\Catalog\Model\Layer\Category\CollectionFilter|\PHPUnit\Framework\MockObject\MockObject
     */
    private $collectionFilter;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $collection;

    /**
     * @var \Magento\Catalog\Model\Layer\ItemCollectionProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $collectionProvider;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\Item|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filter;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\AbstractFilter|\PHPUnit\Framework\MockObject\MockObject
     */
    private $abstractFilter;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $categoryRepository;

    /**
     * @var \Magento\Catalog\Model\Category|\PHPUnit\Framework\MockObject\MockObject
     */
    private $currentCategory;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->setMethods(['getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->setMethods(['registry'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->setMethods(['getRootCategoryId', 'getFilters', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->setMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->any())->method('getStore')
            ->willReturn($this->store);

        $this->stateKeyGenerator = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Category\StateKey::class)
            ->setMethods(['toString'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFilter = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Category\CollectionFilter::class)
            ->setMethods(['filter'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionProvider = $this->getMockBuilder(
            \Magento\Catalog\Model\Layer\ItemCollectionProviderInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $this->filter = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Filter\Item::class)
            ->setMethods(['getFilter', 'getValueString'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->abstractFilter = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Filter\AbstractFilter::class)
            ->setMethods(['getRequestVar'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(\Magento\Catalog\Model\Layer\ContextInterface::class)
            ->setMethods(['getStateKey', 'getCollectionFilter'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->context->expects($this->any())->method('getStateKey')
            ->willReturn($this->stateKeyGenerator);
        $this->context->expects($this->any())->method('getCollectionFilter')
            ->willReturn($this->collectionFilter);
        $this->context->expects($this->any())->method('getCollectionProvider')
            ->willReturn($this->collectionProvider);

        $this->state = $this->getMockBuilder(\Magento\Catalog\Model\Layer\State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stateFactory = $this->getMockBuilder(\Magento\Catalog\Model\Layer\StateFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->stateFactory->expects($this->any())->method('create')->willReturn($this->state);

        $this->collection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->categoryRepository = $this->createMock(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
        $this->currentCategory = $this->createPartialMock(
            \Magento\Catalog\Model\Category::class,
            ['getId', '__wakeup']
        );

        $this->model = $helper->getObject(
            \Magento\Catalog\Model\Layer::class,
            [
                'registry' => $this->registry,
                'storeManager' => $this->storeManager,
                'context' => $this->context,
                'layerStateFactory' => $this->stateFactory,
                'categoryRepository' => $this->categoryRepository,
            ]
        );
    }

    public function testGetState()
    {
        $this->assertInstanceOf(\Magento\Catalog\Model\Layer\State::class, $this->model->getState());
    }

    public function testGetStateKey()
    {
        $stateKey = 'sk';
        $this->registry->expects($this->once())->method('registry')->with($this->equalTo('current_category'))
            ->willReturn($this->category);

        $this->stateKeyGenerator->expects($this->once())->method('toString')
            ->with($this->equalTo($this->category))
            ->willReturn($stateKey);

        $this->assertEquals($stateKey, $this->model->getStateKey());
    }

    public function testGetProductCollection()
    {
        $this->registry->expects($this->once())->method('registry')->with($this->equalTo('current_category'))
            ->willReturn($this->category);

        $this->category->expects($this->any())->method('getId')->willReturn(333);

        $this->collectionFilter->expects($this->once())->method('filter')
            ->with($this->equalTo($this->collection), $this->equalTo($this->category));

        $this->collectionProvider->expects($this->once())->method('getCollection')
            ->with($this->equalTo($this->category))
            ->willReturn($this->collection);

        $result = $this->model->getProductCollection();
        $this->assertInstanceOf(\Magento\Catalog\Model\ResourceModel\Product\Collection::class, $result);
        $result = $this->model->getProductCollection();
        $this->assertInstanceOf(\Magento\Catalog\Model\ResourceModel\Product\Collection::class, $result);
    }

    public function testApply()
    {
        $stateKey = 'sk';
        $this->registry->expects($this->once())->method('registry')->with($this->equalTo('current_category'))
            ->willReturn($this->category);

        $this->stateKeyGenerator->expects($this->once())->method('toString')
            ->with($this->equalTo($this->category))
            ->willReturn($stateKey);

        $this->state->expects($this->any())->method('getFilters')->willReturn([$this->filter]);

        $this->filter->expects($this->once())->method('getFilter')->willReturn($this->abstractFilter);
        $this->filter->expects($this->once())->method('getValueString')->willReturn('t');

        $this->abstractFilter->expects($this->once())->method('getRequestVar')->willReturn('t');

        $result = $this->model->apply();
        $this->assertInstanceOf(\Magento\Catalog\Model\Layer::class, $result);
    }

    public function testPrepareProductCollection()
    {
        $this->registry->expects($this->once())->method('registry')->with($this->equalTo('current_category'))
            ->willReturn($this->category);

        $this->collectionFilter->expects($this->once())->method('filter')
            ->with($this->equalTo($this->collection), $this->equalTo($this->category));

        $result = $this->model->prepareProductCollection($this->collection);
        $this->assertInstanceOf(\Magento\Catalog\Model\Layer::class, $result);
    }

    public function testGetCurrentStore()
    {
        $this->assertInstanceOf(\Magento\Store\Model\Store::class, $this->model->getCurrentStore());
    }

    public function testSetNewCurrentCategoryIfCurrentCategoryIsAnother()
    {
        $categoryId = 333;
        $currentCategoryId = 334;

        $this->category->expects($this->any())->method('getId')->willReturn($categoryId);
        $this->categoryRepository->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($this->currentCategory);

        $this->currentCategory->expects($this->any())->method('getId')->willReturn($currentCategoryId);
        $this->registry->expects($this->once())->method('registry')->with('current_category')
            ->willReturn($this->currentCategory);

        $this->assertInstanceOf(\Magento\Catalog\Model\Layer::class, $this->model->setCurrentCategory($categoryId));
        $this->assertEquals($this->currentCategory, $this->model->getData('current_category'));
    }

    public function testSetNewCurrentCategoryIfCurrentCategoryIsSame()
    {
        $categoryId = 333;

        $this->category->expects($this->any())->method('getId')->willReturn($categoryId);

        $this->categoryRepository->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($this->category);
        $this->registry->expects($this->once())->method('registry')->with('current_category')
            ->willReturn($this->category);

        $this->assertInstanceOf(\Magento\Catalog\Model\Layer::class, $this->model->setCurrentCategory($categoryId));
        $this->assertEquals($this->category, $this->model->getData('current_category'));
    }

    /**
     */
    public function testSetNewCurrentCategoryIfCategoryIsNotFound()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Please correct the category.');

        $this->categoryRepository->expects($this->once())->method('get')
            ->will($this->throwException(new NoSuchEntityException()));

        $this->model->setCurrentCategory(1);
    }

    /**
     */
    public function testSetCurrentCategoryInstanceOfException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Must be category model instance or its id.');

        $this->model->setCurrentCategory(null);
    }

    /**
     */
    public function testSetCurrentCategoryNotFoundException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Please correct the category.');

        $this->category->expects($this->once())->method('getId')->willReturn(null);

        $this->model->setCurrentCategory($this->category);
    }

    public function testGetCurrentCategory()
    {
        $this->currentCategory->getData('current_category', null);

        $this->registry->expects($this->once())->method('registry')->with('current_category')
            ->willReturn($this->currentCategory);

        $this->assertEquals($this->currentCategory, $this->model->getCurrentCategory());
        $this->assertEquals($this->currentCategory, $this->model->getData('current_category'));
    }

    public function testGetCurrentCategoryIfCurrentCategoryIsNotSet()
    {
        $rootCategoryId = 333;
        $this->currentCategory->getData('current_category', null);

        $this->registry->expects($this->once())->method('registry')->with($this->equalTo('current_category'))
            ->willReturn(null);
        $this->categoryRepository->expects($this->once())->method('get')->with($rootCategoryId)
            ->willReturn($this->currentCategory);
        $this->store->expects($this->any())->method('getRootCategoryId')
            ->willReturn($rootCategoryId);

        $this->assertEquals($this->currentCategory, $this->model->getCurrentCategory());
        $this->assertEquals($this->currentCategory, $this->model->getData('current_category'));
    }
}

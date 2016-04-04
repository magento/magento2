<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class LayerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Layer
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private $category;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /**
     * @var \Magento\Catalog\Model\Layer\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Catalog\Model\Layer\Category\StateKey|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateKeyGenerator;

    /**
     * @var \Magento\Catalog\Model\Layer\StateFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateFactory;

    /**
     * @var \Magento\Catalog\Model\Layer\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $state;

    /**
     * @var \Magento\Catalog\Model\Layer\Category\CollectionFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFilter;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    /**
     * @var \Magento\Catalog\Model\Layer\ItemCollectionProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProvider;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filter;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\AbstractFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractFilter;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryRepository;

    /**
     * @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private $currentCategory;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->category = $this->getMockBuilder('Magento\Catalog\Model\Category')
            ->setMethods(['getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder('Magento\Framework\Registry')
            ->setMethods(['registry'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->setMethods(['getRootCategoryId', 'getFilters', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->setMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->any())->method('getStore')
            ->will($this->returnValue($this->store));

        $this->stateKeyGenerator = $this->getMockBuilder('Magento\Catalog\Model\Layer\Category\StateKey')
            ->setMethods(['toString'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFilter = $this->getMockBuilder('Magento\Catalog\Model\Layer\Category\CollectionFilter')
            ->setMethods(['filter'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionProvider = $this->getMockBuilder('Magento\Catalog\Model\Layer\ItemCollectionProviderInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->filter = $this->getMockBuilder('Magento\Catalog\Model\Layer\Filter\Item')
            ->setMethods(['getFilter', 'getValueString'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->abstractFilter = $this->getMockBuilder('Magento\Catalog\Model\Layer\Filter\AbstractFilter')
            ->setMethods(['getRequestVar'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder('Magento\Catalog\Model\Layer\ContextInterface')
            ->setMethods(['getStateKey', 'getCollectionFilter'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->context->expects($this->any())->method('getStateKey')
            ->will($this->returnValue($this->stateKeyGenerator));
        $this->context->expects($this->any())->method('getCollectionFilter')
            ->will($this->returnValue($this->collectionFilter));
        $this->context->expects($this->any())->method('getCollectionProvider')
            ->will($this->returnValue($this->collectionProvider));

        $this->state = $this->getMockBuilder('Magento\Catalog\Model\Layer\State')
            ->disableOriginalConstructor()
            ->getMock();

        $this->stateFactory = $this->getMockBuilder('Magento\Catalog\Model\Layer\StateFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->stateFactory->expects($this->any())->method('create')->will($this->returnValue($this->state));

        $this->collection = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Product\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->categoryRepository = $this->getMock('Magento\Catalog\Api\CategoryRepositoryInterface');
        $this->currentCategory = $this->getMock('Magento\Catalog\Model\Category', ['getId', '__wakeup'], [], '', false);

        $this->model = $helper->getObject(
            'Magento\Catalog\Model\Layer',
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
        $this->assertInstanceOf('\Magento\Catalog\Model\Layer\State', $this->model->getState());
    }

    public function testGetStateKey()
    {
        $stateKey = 'sk';
        $this->registry->expects($this->once())->method('registry')->with($this->equalTo('current_category'))
            ->will($this->returnValue($this->category));

        $this->stateKeyGenerator->expects($this->once())->method('toString')
            ->with($this->equalTo($this->category))
            ->will($this->returnValue($stateKey));

        $this->assertEquals($stateKey, $this->model->getStateKey());
    }

    public function testGetProductCollection()
    {
        $this->registry->expects($this->once())->method('registry')->with($this->equalTo('current_category'))
            ->will($this->returnValue($this->category));

        $this->category->expects($this->any())->method('getId')->will($this->returnValue(333));

        $this->collectionFilter->expects($this->once())->method('filter')
            ->with($this->equalTo($this->collection), $this->equalTo($this->category));

        $this->collectionProvider->expects($this->once())->method('getCollection')
            ->with($this->equalTo($this->category))
            ->will($this->returnValue($this->collection));

        $result = $this->model->getProductCollection();
        $this->assertInstanceOf('\Magento\Catalog\Model\ResourceModel\Product\Collection', $result);
        $result = $this->model->getProductCollection();
        $this->assertInstanceOf('\Magento\Catalog\Model\ResourceModel\Product\Collection', $result);
    }

    public function testApply()
    {
        $stateKey = 'sk';
        $this->registry->expects($this->once())->method('registry')->with($this->equalTo('current_category'))
            ->will($this->returnValue($this->category));

        $this->stateKeyGenerator->expects($this->once())->method('toString')
            ->with($this->equalTo($this->category))
            ->will($this->returnValue($stateKey));

        $this->state->expects($this->any())->method('getFilters')->will($this->returnValue([$this->filter]));

        $this->filter->expects($this->once())->method('getFilter')->will($this->returnValue($this->abstractFilter));
        $this->filter->expects($this->once())->method('getValueString')->will($this->returnValue('t'));

        $this->abstractFilter->expects($this->once())->method('getRequestVar')->will($this->returnValue('t'));

        $result = $this->model->apply();
        $this->assertInstanceOf('\Magento\Catalog\Model\Layer', $result);
    }

    public function testPrepareProductCollection()
    {
        $this->registry->expects($this->once())->method('registry')->with($this->equalTo('current_category'))
            ->will($this->returnValue($this->category));

        $this->collectionFilter->expects($this->once())->method('filter')
            ->with($this->equalTo($this->collection), $this->equalTo($this->category));

        $result = $this->model->prepareProductCollection($this->collection);
        $this->assertInstanceOf('\Magento\Catalog\Model\Layer', $result);
    }

    public function testGetCurrentStore()
    {
        $this->assertInstanceOf('\Magento\Store\Model\Store', $this->model->getCurrentStore());
    }

    public function testSetNewCurrentCategoryIfCurrentCategoryIsAnother()
    {
        $categoryId = 333;
        $currentCategoryId = 334;

        $this->category->expects($this->any())->method('getId')->will($this->returnValue($categoryId));
        $this->categoryRepository->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($this->category);

        $this->currentCategory->expects($this->any())->method('getId')->willReturn($currentCategoryId);
        $this->registry->expects($this->once())->method('registry')->with('current_category')
            ->willReturn($this->currentCategory);

        $this->assertInstanceOf('\Magento\Catalog\Model\Layer', $this->model->setCurrentCategory($categoryId));
        $this->assertEquals($this->currentCategory, $this->model->getData('current_category'));
    }

    public function testSetNewCurrentCategoryIfCurrentCategoryIsSame()
    {
        $categoryId = 333;

        $this->category->expects($this->any())->method('getId')->will($this->returnValue($categoryId));

        $this->categoryRepository->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($this->category);
        $this->registry->expects($this->once())->method('registry')->with('current_category')
            ->willReturn($this->category);

        $this->assertInstanceOf('\Magento\Catalog\Model\Layer', $this->model->setCurrentCategory($categoryId));
        $this->assertEquals($this->category, $this->model->getData('current_category'));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please correct the category.
     */
    public function testSetNewCurrentCategoryIfCategoryIsNotFound()
    {
        $this->categoryRepository->expects($this->once())->method('get')
            ->will($this->throwException(new NoSuchEntityException()));

        $this->model->setCurrentCategory(1);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Must be category model instance or its id.
     */
    public function testSetCurrentCategoryInstanceOfException()
    {
        $this->model->setCurrentCategory(null);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please correct the category.
     */
    public function testSetCurrentCategoryNotFoundException()
    {
        $this->category->expects($this->once())->method('getId')->will($this->returnValue(null));

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
            ->willReturn($this->category);
        $this->store->expects($this->any())->method('getRootCategoryId')
            ->will($this->returnValue($rootCategoryId));

        $this->assertEquals($this->currentCategory, $this->model->getCurrentCategory());
        $this->assertEquals($this->currentCategory, $this->model->getData('current_category'));
    }
}

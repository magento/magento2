<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model;

use Magento\TestFramework\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var \Magento\Catalog\Model\CategoryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryFactory;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Catalog\Model\Resource\Product\Collection|\PHPUnit_Framework_MockObject_MockObject
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

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->category = $this->getMockBuilder('Magento\Catalog\Model\Category')
            ->setMethods(['load', 'getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->categoryFactory = $this->getMockBuilder('Magento\Catalog\Model\CategoryFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryFactory->expects($this->any())->method('create')
            ->will($this->returnValue($this->category));

        $this->registry = $this->getMockBuilder('Magento\Framework\Registry')
            ->setMethods(['registry'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->setMethods(['getRootCategoryId', 'getFilters', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->storeManager = $this->getMockBuilder('Magento\Framework\StoreManagerInterface')
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

        $this->collection = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $helper->getObject(
            'Magento\Catalog\Model\Layer',
            [
                'registry' => $this->registry,
                'categoryFactory' => $this->categoryFactory,
                'storeManager' => $this->storeManager,
                'context' => $this->context,
                'layerStateFactory' => $this->stateFactory
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
        $this->assertInstanceOf('\Magento\Catalog\Model\Resource\Product\Collection', $result);
        $result = $this->model->getProductCollection();
        $this->assertInstanceOf('\Magento\Catalog\Model\Resource\Product\Collection', $result);
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

    public function testSetCurrentCategory()
    {
        $categoryId = 333;

        $this->category->expects($this->once())->method('load')->with($this->equalTo($categoryId))
            ->will($this->returnValue($this->category));
        $this->category->expects($this->at(0))->method('getId')->will($this->returnValue($categoryId));
        $this->category->expects($this->at(1))->method('getId')->will($this->returnValue($categoryId));
        $this->category->expects($this->at(2))->method('getId')->will($this->returnValue($categoryId - 1));

        $this->registry->expects($this->once())->method('registry')->with($this->equalTo('current_category'))
            ->will($this->returnValue($this->category));

        $result = $this->model->setCurrentCategory($categoryId);
        $this->assertInstanceOf('\Magento\Catalog\Model\Layer', $result);
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage The category must be an instance of \Magento\Catalog\Model\Category.
     */
    public function testSetCurrentCategoryInstanceOfException()
    {
        $this->model->setCurrentCategory(null);
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Please correct the category.
     */
    public function testSetCurrentCategoryNotFoundException()
    {
        $this->category->expects($this->at(0))->method('getId')->will($this->returnValue(null));

        $this->model->setCurrentCategory($this->category);
    }

    /**
     * @dataProvider currentCategoryProvider
     */
    public function testGetCurrentCategory($currentCategory)
    {
        $rootCategoryId = 333;
        $this->registry->expects($this->once())->method('registry')->with($this->equalTo('current_category'))
            ->will($this->returnValue($currentCategory));

        $this->category->expects($this->any())->method('load')->with($this->equalTo($rootCategoryId))
            ->will($this->returnValue($this->category));

        $this->store->expects($this->any())->method('getRootCategoryId')
            ->will($this->returnValue($rootCategoryId));

        $result = $this->model->getCurrentCategory();
        $this->assertInstanceOf('\Magento\Catalog\Model\Category', $result);
    }

    public function currentCategoryProvider()
    {
        $category = $this->getMockBuilder('Magento\Catalog\Model\Category')
            ->disableOriginalConstructor()
            ->getMock();

        return [[$category], [null]];
    }
}
